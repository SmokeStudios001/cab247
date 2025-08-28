<?php
// admin-panel/includes/footer.php
?>
            </div>
            
            <footer class="admin-footer">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $company_name; ?>. All rights reserved.</p>
            </footer>
        </div>
    </div>
    
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    
    <!-- Page-specific scripts -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>