<?php if(isset($total_main) && isset($total_reserve)): ?>
<div class="top-cards">
    <div class="card big-card">
        <div class="sub-card">
            <div class="icon red">📦</div>
            <div class="number"><?= html_escape($total_main) ?></div>
            <div class="label">Total Main Stock</div>
        </div>
        <div class="divider"></div>
        <div class="sub-card">
            <div class="icon yellow">📦</div>
            <div class="number"><?= html_escape($total_reserve) ?></div>
            <div class="label">Total Reserve Stock</div>
        </div>
    </div>
</div>
<?php endif; ?>
