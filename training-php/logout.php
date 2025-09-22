<script>
    // ✅ Xóa dữ liệu đăng nhập khỏi localStorage
    localStorage.removeItem('user_login');
    localStorage.removeItem('remembered_username'); // nếu có checkbox remember

    // ✅ Chuyển về trang đăng nhập
    location.href = 'login.php';
</script>
