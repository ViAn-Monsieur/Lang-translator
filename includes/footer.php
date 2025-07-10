<?php if (isset($is_admin) && $is_admin): ?>
            </div> <!-- container-fluid -->
        </main> <!-- admin-main -->
    </div> <!-- admin-content -->
<?php else: ?>
    </div> <!-- container -->
<?php endif; ?>
<!-- Footer -->
<!-- Footer -->
<footer class="site-footer">
  <p>🌐 Dự án: Website Dịch Ngôn Ngữ</p>
  <p>Thành viên: Trần Vĩ Ân, Lê Nhật Tân, Nguyễn Đức Trung</p>
  <p>🔗 Xem thêm: <a href="https://vian-monsieur.github.io/Digital-CV/" target="_blank">Giới thiệu các thành viên trong nhóm</a></p>
  <p>&copy; <?= date('Y') ?> UTH - Trường Đại học Giao Thông Vận Tải TP.HCM</p>
</footer>

<!-- JavaScript for sidebar (only load if exists) -->
<?php if (file_exists('js/sidebar.js')): ?>
    <script src="js/sidebar.js"></script>
<?php elseif (file_exists('../js/sidebar.js')): ?>
    <script src="../js/sidebar.js"></script>
<?php endif; ?>

</body>
</html>
