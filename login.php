<?php
session_start();
if (isset($_POST['login'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'kemuning99') {
        $_SESSION['admin_logged'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Es Teh Kemuning</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-slate-800 border border-slate-700 p-6 rounded-3xl max-w-sm w-full shadow-2xl">
        <h1 class="text-lg font-bold text-center mb-4">Login Admin Kemuning</h1>
        <?php if(isset($error)) echo "<p class='text-red-400 text-xs text-center mb-3'>$error</p>"; ?>
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs text-slate-300 mb-1">Username</label>
                <input type="text" name="username" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs text-slate-300 mb-1">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white">
            </div>
            <button type="submit" name="login" class="w-full bg-amber-500 text-slate-950 font-bold py-2.5 rounded-xl text-sm">Masuk</button>
        </form>
    </div>
</body>
</html>