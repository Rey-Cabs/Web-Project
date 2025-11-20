// dashboard-chart.js
// Fetches aggregated patient counts and renders the Chart.js bar chart

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('barChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let chart = null;
    const buildChart = (labels, data) => {
        // ensure we always render something; if empty, show zero
        if (!Array.isArray(labels) || labels.length === 0) {
            labels = ['No data'];
        }
        if (!Array.isArray(data) || data.length === 0) {
            data = [0];
        }
        // if data shorter than labels, pad with zeros
        if (data.length < labels.length) {
            data = data.concat(new Array(labels.length - data.length).fill(0));
        }

        if (chart) chart.destroy();
        chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Incoming Patients',
                    data: data,
                    backgroundColor: '#b73b2f'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
            }
        });
    };

    const fetchAndRender = async (period) => {
        try {
            const url = (window.siteBasePath || '') + '/patients_chart?period=' + encodeURIComponent(period);
            const res = await fetch(url, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Network response was not ok');
            const json = await res.json();
            buildChart(json.labels || [], json.data || []);
        } catch (err) {
            console.error('Failed to load chart data', err);
        }
    };

    // attach to buttons
    const btns = document.querySelectorAll('.chart-btn');
    btns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            btns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const range = btn.dataset.range || 'weekly';
            fetchAndRender(range);
        });
    });

    // initial load: weekly
    const active = document.querySelector('.chart-btn.active') || document.querySelector('.chart-btn[data-range="weekly"]');
    const initial = active ? (active.dataset.range || 'weekly') : 'weekly';
    fetchAndRender(initial);

    // PIE CHART: disease breakdown
    const pieCanvas = document.getElementById('pieChart');
    if (pieCanvas) {
        const pieCtx = pieCanvas.getContext('2d');
        let pieChart = null;
        const renderPie = (labels, data) => {
            if (pieChart) pieChart.destroy();
            pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: { labels: labels, datasets: [{ data: data, backgroundColor: ['#f28b82','#fbbc04','#34a853','#4285f4','#9b59b6','#e67e22','#95a5a6'] }] },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        };

        (async () => {
            try {
                const res = await fetch((window.siteBasePath || '') + '/patients_disease', { credentials: 'same-origin' });
                if (!res.ok) throw new Error('Network response was not ok');
                const json = await res.json();
                let pLabels = json.labels || [];
                let pData = json.data || [];
                if (!Array.isArray(pLabels) || pLabels.length === 0) { pLabels = ['No data']; }
                if (!Array.isArray(pData) || pData.length === 0) { pData = [0]; }
                renderPie(pLabels, pData);
            } catch (err) {
                console.error('Failed to load disease chart', err);
            }
        })();
    }

    // PREDICTIVE CHART: next period prediction
    const lineCanvas = document.getElementById('lineChart');
    if (lineCanvas) {
        const lineCtx = lineCanvas.getContext('2d');
        let lineChart = null;
        const renderLine = (labels, data) => {
            if (lineChart) lineChart.destroy();
            lineChart = new Chart(lineCtx, {
                type: 'bar',
                data: { labels: labels, datasets: [{ label: 'Predicted New Patients', data: data, backgroundColor: 'rgba(183,59,47,0.6)' }] },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        };

        const fetchPredict = async (period) => {
            try {
                const res = await fetch((window.siteBasePath || '') + '/patients_predict?period=' + encodeURIComponent(period), { credentials: 'same-origin' });
                if (!res.ok) throw new Error('Network response was not ok');
                const json = await res.json();
                let lLabels = json.labels || [];
                let lData = json.data || [];
                if (!Array.isArray(lLabels) || lLabels.length === 0) { lLabels = ['Prediction']; }
                if (!Array.isArray(lData) || lData.length === 0) { lData = [0]; }
                renderLine(lLabels, lData);
            } catch (err) {
                console.error('Failed to load prediction data', err);
            }
        };

        // initially load prediction for initial period
        fetchPredict(initial);

        // when chart range buttons change, update prediction too
        btns.forEach(b => b.addEventListener('click', () => {
            const range = b.dataset.range || 'weekly';
            fetchPredict(range);
        }));
    }
});
