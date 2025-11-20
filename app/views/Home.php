<?php
// home.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthSync - Your Trusted Health Partner</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <script src="<?= base_url(); ?>public/JS/script.js"></script>
</head>
<body>

    <?php include('templates/header.php'); ?>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <h1>Your Health, Our Priority</h1>
            <p>Providing quality healthcare services with trust and care. Your wellbeing is our mission.</p>
            <a href="<?= site_url('/appointments') ?>" class="btn-primary">Book an Appointment</a>
        </div>
    </section>

    <!-- SERVICES SECTION -->
    <section class="services">
        <h2>Our Services</h2>
        <div class="services-cards">
            <div class="service-card">
                <div class="service-icon">🩺</div>
                <h3>General Consultation</h3>
                <p>Comprehensive medical consultation to keep you healthy and informed.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">💊</div>
                <h3>Pharmacy & Medications</h3>
                <p>Reliable prescriptions and medication services with expert guidance.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">📅</div>
                <h3>Appointments & Scheduling</h3>
                <p>Easy scheduling of appointments with our experienced medical staff.</p>
            </div>
            <div class="service-card">
                <div class="service-icon">📋</div>
                <h3>Medical Records</h3>
                <p>Secure and accessible records to ensure continuity of care.</p>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about">
        <div class="about-content">
            <h2>About HealthSync</h2>
            <p>HealthSync is a trusted healthcare provider committed to delivering quality care for patients of all ages. Our team of experienced doctors, nurses, and healthcare professionals ensure that your health and safety are our top priority.</p>
            <a href="<?= site_url('/about') ?>" class="btn-secondary">Learn More</a>
        </div>
        <div class="about-image">
            <img src="<?= base_url(); ?>public/IMG/img.jpg" alt="Our Healthcare Team">
        </div>
    </section>

    <!-- CONTACT / CALL TO ACTION -->
    <section class="cta">
        <h2>Need Medical Assistance?</h2>
        <p>Contact us today or book an appointment online. Your health deserves the best care.</p>
        <a href="<?= site_url('/contact') ?>" class="btn-primary">Contact Us</a>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
