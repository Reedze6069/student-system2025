<?php if (isset($_SESSION['error'])): ?>
    <div style="background-color: #ffdddd; color: #a94442; border: 1px solid #ebccd1; padding: 10px; margin: 20px auto; max-width: 800px; border-radius: 5px; text-align: center;">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php elseif (isset($_SESSION['success'])): ?>
    <div style="background-color: #ddffdd; color: #3c763d; border: 1px solid #d6e9c6; padding: 10px; margin: 20px auto; max-width: 800px; border-radius: 5px; text-align: center;">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<footer class="site-footer">
    <div class="footer-content">
        <p>&copy; <?php echo date('Y'); ?> Student Management System. All rights reserved.</p>

        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <span>|</span>
            <a href="#">Terms of Service</a>
            <span>|</span>
            <a href="#">Contact</a>
        </div>
    </div>
</footer>
