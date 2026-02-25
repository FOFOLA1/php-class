<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="output.css">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Login</h1>
        <?php if (isset($error) && $error): ?>
            <p class="text-red-600 bg-red-50 border border-red-200 rounded p-3 mb-4 text-sm"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" action="index.php?action=login" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username:</label>
                <input type="text" name="username" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password:</label>
                <input type="password" name="password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium">
                Login
            </button>
        </form>
        <p class="mt-4 text-sm text-gray-500 text-center">Default admin: admin / admin (needs to be created manually or via seed)</p>
        <p class="mt-2 text-sm text-center text-gray-600">Don't have an account? <a href="index.php?action=register" class="text-blue-600 hover:underline">Register here</a></p>
    </div>
</body>

</html>