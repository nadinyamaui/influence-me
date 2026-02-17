import Chart from 'chart.js/auto';

window.Chart = Chart;

window.audienceGrowthChart = (labels, data) => ({
    chart: null,
    labels,
    data,
    init() {
        this.chart = new Chart(this.$refs.canvas, {
            type: 'line',
            data: {
                labels: this.labels,
                datasets: [
                    {
                        label: 'Followers',
                        data: this.data,
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13, 148, 136, 0.08)',
                        tension: 0.35,
                        fill: true,
                        borderWidth: 2,
                        pointRadius: 2,
                    },
                ],
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: false,
                    },
                },
            },
        });
    },
});
