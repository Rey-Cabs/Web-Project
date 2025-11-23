// dashboard-chart.js
document.addEventListener('DOMContentLoaded', () => {
    const siteBase = window.siteBasePath || '';

    /*** BAR CHART: Incoming Patients ***/
    const barCanvas = document.getElementById('barChart');
    let barChart = null;
    const buildBarChart = (labels, data) => {
        if (!Array.isArray(labels) || labels.length === 0) labels = ['No data'];
        if (!Array.isArray(data) || data.length === 0) data = [0];
        if (data.length < labels.length) data = data.concat(new Array(labels.length - data.length).fill(0));

        if (barChart) barChart.destroy();
        barChart = new Chart(barCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: 'Incoming Patients', data: data, backgroundColor: '#b73b2f' }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    };

    /*** PIE CHART: Patients Diseases ***/
    const pieCanvas = document.getElementById('pieChart');
    let pieChart = null;
    const buildPieChart = (labels, data) => {
        if (!Array.isArray(labels) || labels.length === 0) labels = ['No data'];
        if (!Array.isArray(data) || data.length === 0) data = [0];

        if (pieChart) pieChart.destroy();
        pieChart = new Chart(pieCanvas.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{ 
                    data: data,
                    backgroundColor: ['#f28b82','#fbbc04','#34a853','#4285f4','#9b59b6','#e67e22','#95a5a6']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    };

    /*** LINE / PREDICTIVE CHART: New Patients ***/
    const lineCanvas = document.getElementById('lineChart');
    let lineChart = null;
    const buildLineChart = (labels, data) => {
        if (!Array.isArray(labels) || labels.length === 0) labels = ['Prediction'];
        if (!Array.isArray(data) || data.length === 0) data = [0];

        if (lineChart) lineChart.destroy();
        lineChart = new Chart(lineCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: 'Predicted New Patients', data: data, backgroundColor: 'rgba(183,59,47,0.6)' }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    };

    /*** Fetch and render bar chart ***/
    const fetchBarChart = async (period='weekly') => {
        try {
            const res = await fetch(`${siteBase}/patients_chart?period=${encodeURIComponent(period)}`, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Network response was not ok');
            const json = await res.json();
            buildBarChart(json.labels || [], json.data || []);
        } catch (err) {
            console.error('Failed to load bar chart data', err);
        }
    };

    /*** Fetch and render pie chart ***/
    const fetchPieChart = async () => {
        if (!pieCanvas) return;
        try {
            const res = await fetch(`${siteBase}/patients_disease`, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Network response was not ok');
            const json = await res.json();

            // transform controller output into labels and data
            const labels = json.map(d => d.disease);
            const data   = json.map(d => d.count);

            buildPieChart(labels, data);
        } catch (err) {
            console.error('Failed to load disease chart', err);
        }
    };

    /*** Fetch and render predictive line chart ***/
    const fetchLineChart = async (period='weekly') => {
        if (!lineCanvas) return;
        try {
            const res = await fetch(`${siteBase}/patients_predict?period=${encodeURIComponent(period)}`, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Network response was not ok');
            const json = await res.json();

            const labels = (json.historical || []).map((d, i) => d.label || `Period ${i+1}`);
            const data   = json.predicted || [];
            buildLineChart(labels, data);
        } catch (err) {
            console.error('Failed to load prediction chart', err);
        }
    };

    /*** Chart period buttons ***/
    const chartBtns = document.querySelectorAll('.chart-btn');
    chartBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            chartBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const period = btn.dataset.range || 'weekly';
            fetchBarChart(period);
            fetchLineChart(period);
        });
    });

    /*** Initial load ***/
    const initialPeriod = document.querySelector('.chart-btn.active')?.dataset.range || 'weekly';
    fetchBarChart(initialPeriod);
    fetchPieChart();
    fetchLineChart(initialPeriod);
});
