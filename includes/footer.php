<?php
// includes/footer.php
if (!isset($hide_footer)) {
?>
    </div> <!-- End main content -->
    
    <script src="../assets/js/utils.js"></script>
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="../assets/js/<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
<?php } ?>