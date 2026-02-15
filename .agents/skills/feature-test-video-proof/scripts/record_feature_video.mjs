#!/usr/bin/env node

import fs from 'node:fs/promises';
import path from 'node:path';
import os from 'node:os';
import process from 'node:process';
import { spawn } from 'node:child_process';
import puppeteer from 'puppeteer';

const DEFAULT_VIEWPORT = { width: 1440, height: 900 };
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function parseArgs(argv) {
    const args = new Map();

    for (let i = 2; i < argv.length; i += 1) {
        const token = argv[i];
        if (!token.startsWith('--')) {
            continue;
        }

        const key = token.slice(2);
        const value = argv[i + 1]?.startsWith('--') || argv[i + 1] === undefined
            ? 'true'
            : argv[++i];
        args.set(key, value);
    }

    return args;
}

function ensureArg(args, key, helpText) {
    const value = args.get(key);
    if (!value) {
        throw new Error(`Missing --${key}\n\n${helpText}`);
    }

    return value;
}

function parseNumber(value, fallback, label) {
    if (value === undefined) {
        return fallback;
    }

    const parsed = Number.parseInt(value, 10);
    if (Number.isNaN(parsed) || parsed <= 0) {
        throw new Error(`Invalid ${label}: ${value}`);
    }

    return parsed;
}

async function mkdirp(dirPath) {
    await fs.mkdir(dirPath, { recursive: true });
}

async function runFfmpeg({ inputPattern, fps, outputFile }) {
    await new Promise((resolve, reject) => {
        const ffmpeg = spawn('ffmpeg', [
            '-y',
            '-framerate',
            String(fps),
            '-i',
            inputPattern,
            '-c:v',
            'libx264',
            '-pix_fmt',
            'yuv420p',
            outputFile,
        ]);

        let stderr = '';
        ffmpeg.stderr.on('data', (chunk) => {
            stderr += chunk.toString();
        });

        ffmpeg.on('error', (error) => {
            reject(new Error(`Failed to start ffmpeg: ${error.message}`));
        });

        ffmpeg.on('close', (code) => {
            if (code === 0) {
                resolve();
                return;
            }

            reject(new Error(`ffmpeg failed with exit code ${code}\n${stderr}`));
        });
    });
}

async function replaceEnvPlaceholders(input) {
    if (typeof input === 'string') {
        return input.replace(/\$\{([A-Z0-9_]+)\}/gi, (_, key) => process.env[key] ?? '');
    }

    if (Array.isArray(input)) {
        return Promise.all(input.map(replaceEnvPlaceholders));
    }

    if (input && typeof input === 'object') {
        const entries = await Promise.all(
            Object.entries(input).map(async ([key, value]) => [key, await replaceEnvPlaceholders(value)]),
        );

        return Object.fromEntries(entries);
    }

    return input;
}

async function performAction(page, action, baseUrl) {
    switch (action.type) {
        case 'goto': {
            const url = action.url.startsWith('http')
                ? action.url
                : `${baseUrl.replace(/\/$/, '')}/${action.url.replace(/^\//, '')}`;
            await page.goto(url, { waitUntil: action.waitUntil ?? 'networkidle2' });
            return `goto:${url}`;
        }
        case 'waitForSelector':
            await page.waitForSelector(action.selector, { visible: action.visible ?? true, timeout: action.timeout ?? 30000 });
            return `waitForSelector:${action.selector}`;
        case 'click':
            await page.click(action.selector, action.clickOptions ?? {});
            return `click:${action.selector}`;
        case 'type':
            if (action.clear ?? true) {
                await page.click(action.selector, { clickCount: 3 });
                await page.keyboard.press('Backspace');
            }
            await page.type(action.selector, action.text ?? '', { delay: action.delay ?? 0 });
            return `type:${action.selector}`;
        case 'press':
            await page.keyboard.press(action.key);
            return `press:${action.key}`;
        case 'select':
            await page.select(action.selector, ...(action.values ?? []));
            return `select:${action.selector}`;
        case 'waitForNavigation':
            await page.waitForNavigation({ waitUntil: action.waitUntil ?? 'networkidle2', timeout: action.timeout ?? 30000 });
            return 'waitForNavigation';
        case 'waitForTimeout':
            await sleep(action.ms ?? 1000);
            return `waitForTimeout:${action.ms ?? 1000}`;
        case 'assertText': {
            await page.waitForFunction(
                ({ selector, expected }) => {
                    const element = selector ? document.querySelector(selector) : document.body;
                    return !!element && element.textContent?.includes(expected);
                },
                { timeout: action.timeout ?? 30000 },
                { selector: action.selector ?? null, expected: action.text ?? '' },
            );
            return `assertText:${action.text ?? ''}`;
        }
        default:
            throw new Error(`Unsupported action type: ${action.type}`);
    }
}

async function main() {
    const helpText = [
        'Usage:',
        '  node record_feature_video.mjs --base-url http://127.0.0.1:8000 --actions /path/actions.json --output /path/video.mp4',
        '',
        'Optional:',
        '  --fps 6 --width 1440 --height 900 --hold-ms 1200 --report /path/report.json --keep-frames',
    ].join('\n');

    const args = parseArgs(process.argv);
    const baseUrl = ensureArg(args, 'base-url', helpText);
    const actionsPath = ensureArg(args, 'actions', helpText);
    const outputFile = path.resolve(ensureArg(args, 'output', helpText));
    const reportPath = args.get('report') ? path.resolve(args.get('report')) : `${outputFile}.json`;
    const fps = parseNumber(args.get('fps'), 6, 'fps');
    const width = parseNumber(args.get('width'), DEFAULT_VIEWPORT.width, 'width');
    const height = parseNumber(args.get('height'), DEFAULT_VIEWPORT.height, 'height');
    const holdMs = parseNumber(args.get('hold-ms'), 1200, 'hold-ms');
    const keepFrames = args.get('keep-frames') === 'true';

    const rawActions = JSON.parse(await fs.readFile(path.resolve(actionsPath), 'utf8'));
    const actions = await replaceEnvPlaceholders(rawActions.actions ?? rawActions);

    if (!Array.isArray(actions) || actions.length === 0) {
        throw new Error('Actions file must contain a non-empty array (or an object with an "actions" array).');
    }

    const tempRoot = await fs.mkdtemp(path.join(os.tmpdir(), 'feature-video-'));
    const framesDir = path.join(tempRoot, 'frames');
    await mkdirp(framesDir);
    await mkdirp(path.dirname(outputFile));
    await mkdirp(path.dirname(reportPath));

    const framePattern = path.join(framesDir, 'frame-%06d.png');
    const report = {
        passed: false,
        output_file: outputFile,
        actions_path: path.resolve(actionsPath),
        actions_executed: [],
        error: null,
    };

    let browser;
    let page;
    let frameIndex = 0;
    let recordingInterval;
    let frameInFlight = false;

    const captureFrame = async () => {
        if (!page || frameInFlight) {
            return;
        }

        frameInFlight = true;
        const filePath = path.join(framesDir, `frame-${String(frameIndex).padStart(6, '0')}.png`);
        frameIndex += 1;
        await page.screenshot({ path: filePath });
        frameInFlight = false;
    };

    try {
        browser = await puppeteer.launch({ headless: 'new', defaultViewport: { width, height } });
        page = await browser.newPage();
        page.setDefaultTimeout(30000);

        await captureFrame();
        recordingInterval = setInterval(() => {
            captureFrame().catch(() => {
                // Keep best-effort capture while preserving main test flow.
            });
        }, Math.floor(1000 / fps));

        for (const action of actions) {
            const summary = await performAction(page, action, baseUrl);
            report.actions_executed.push(summary);
            await captureFrame();
        }

        await sleep(holdMs);
        await captureFrame();
        report.passed = true;
    } catch (error) {
        report.error = error instanceof Error ? error.message : String(error);
    } finally {
        if (recordingInterval) {
            clearInterval(recordingInterval);
        }

        if (browser) {
            await browser.close();
        }
    }

    if (frameIndex === 0) {
        throw new Error('No frames captured, cannot build video.');
    }

    await runFfmpeg({
        inputPattern: framePattern,
        fps,
        outputFile,
    });

    await fs.writeFile(reportPath, `${JSON.stringify(report, null, 2)}\n`, 'utf8');

    if (!keepFrames) {
        await fs.rm(tempRoot, { recursive: true, force: true });
    }

    if (!report.passed) {
        throw new Error(`Feature flow failed. See ${reportPath}`);
    }

    console.log(`Video saved: ${outputFile}`);
    console.log(`Report saved: ${reportPath}`);
}

main().catch((error) => {
    console.error(error.message);
    process.exit(1);
});
