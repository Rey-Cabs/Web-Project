<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <script src="<?= base_url(); ?>public/JS/script.js"></script>
</head>
<body>

    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <main class="about-us">
            <section class="about-hero">
                <h1>About HealthSync</h1>
                <p>Delivering trusted healthcare services with compassion and dedication. We focus on your health and wellbeing at every step.</p>
            </section>

            <section class="mission-vision">
                <div class="mv-card">
                    <h2>Our Mission</h2>
                    <p>To provide accessible, high-quality healthcare to all patients while ensuring safety, compassion, and professionalism.</p>
                </div>
                <div class="mv-card">
                    <h2>Our Vision</h2>
                    <p>To be the leading community healthcare provider, recognized for innovation, patient care, and excellence.</p>
                </div>
            </section>

            <section class="values">
                <h2>Our Core Values</h2>
                <div class="values-cards">
                    <div class="value-card">
                        <h3>Compassion</h3>
                        <p>We treat every patient with empathy and respect, providing care with a human touch.</p>
                    </div>
                    <div class="value-card">
                        <h3>Integrity</h3>
                        <p>We uphold the highest standards of honesty, transparency, and professionalism.</p>
                    </div>
                    <div class="value-card">
                        <h3>Excellence</h3>
                        <p>We are committed to continuous improvement and delivering exceptional healthcare services.</p>
                    </div>
                    <div class="value-card">
                        <h3>Innovation</h3>
                        <p>We embrace technology and new methods to improve patient outcomes and services.</p>
                    </div>
                </div>
            </section>

            <section class="team">
                <h2>Meet Our Team</h2>
                <div class="team-cards">
                    <div class="team-card">
                        <img src="<?= base_url(); ?>public/IMG/carl.jpg" alt="Dok Kate">
                        <h4>Crisporo</h4>
                        <p>Chief Medical Office</p>
                    </div>
                    <div class="team-card">
                        <img src="<?= base_url(); ?>public/IMG/koy.jpg" alt="Dok Kate">
                        <h4>Aquino</h4>
                        <p>Senior General Physician</p>
                    </div>
                    <div class="team-card">
                        <img src="<?= base_url(); ?>public/IMG/jay.jpg" alt="Dok Kate">
                        <h4>Cabral</h4>
                        <p>Clinic Administrator</p>
                    </div>
                    <div class="team-card">
                        <img src="<?= base_url(); ?>public/IMG/bakla.jpg" alt="Dok Kate">
                        <h4>Hernandez</h4>
                        <p>Head Nurse</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <?php include('templates/footer.php'); ?>
</body>
</html>
