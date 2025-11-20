<div style="font-family: Arial, sans-serif; background:#f9f5f0; padding:30px;">
    <div style="max-width:500px; margin:auto; background:white; padding:25px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.08);">
        <h2 style="text-align:center; color:#b73b2f; margin-bottom:20px; font-weight:700;">
            HealthSync Verification
        </h2>

        <p style="color:#333; font-size:15px;">
            Hello <strong><?= htmlspecialchars($first_name) ?></strong>,
        </p>

        <p style="color:#333; font-size:15px;">
            Use the verification code below to <?= $purpose === 'login' ? 'log in' : ($purpose === 'signup' ? 'complete signup' : 'reset your password') ?>.
        </p>

        <div style="text-align:center; margin:25px 0;">
            <div style="display:inline-block; padding:15px 35px; background:#b73b2f; color:white; font-size:32px; font-weight:700; letter-spacing:6px; border-radius:8px;">
                <?= $code ?>
            </div>
        </div>

        <p style="color:#555; font-size:14px;">
            This code is valid for <strong>5 minutes</strong>. If you did not request this, please ignore this email.
        </p>

        <hr style="border:none; border-top:1px solid #eee; margin:25px 0;">
        <p style="text-align:center; color:#888; font-size:12px;">
            © <?= date("Y") ?> HealthSync · Secure Verification System
        </p>
    </div>
</div>
