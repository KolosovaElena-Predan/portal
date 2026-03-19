<?php
/**
 * Подвал и скрипты
 */
?>
</div>
<!-- /.content-wrapper -->

<footer class="main-footer">
    <strong>Copyright &copy; <?= date('Y') ?> МИП.</strong> Все права защищены.
</footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>

<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<!-- Summernote -->
<script src="plugins/summernote/summernote-bs4.min.js"></script>

<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>

<!-- Page specific scripts -->
<?php if (isset($pageScript)): ?>
<script>
<?= $pageScript ?>
</script>
<?php endif; ?>

</body>
</html>