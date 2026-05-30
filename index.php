<?php
session_start();

$db = new PDO('sqlite:' . __DIR__ . '/db.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("
    create table if not exists posts ( 
        id integer primary key autoincrement,
        title text not null,
        body text not null,
        created_at text not null default current_timestamp
    )
");

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$action = $_GET['action'] ?? 'home';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_post') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($title !== '' && $body !== '') {
        $stmt = $db->prepare("
            insert into posts (title, body) 
            values (:title, :body)");
        $stmt->execute([
            ':title' => $title,
            ':body' => $body,
        ]);

        header('Location: ?action=posts');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script 
        src="https://code.jquery.com/jquery-4.0.0.min.js" 
        integrity="sha256-OaVG6prZf4v69dPg6PhVattBXkcOWQB62pdZ3ORyrao=" 
        crossorigin="anonymous">
    </script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        ul {
            list-style: none;
            display: flex;
            padding: 0;
            margin: 0;
            gap: 0.5em;
        }

        a {
            text-decoration: none;
            color: black;
        }

        header {
            padding: 1em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content {
            padding: 1em;
            flex: 1;
        }

        form {
            display: grid;
            gap: 0.75em;
            max-width: 560px;
        }

        input,
        textarea,
        button {
            font:inherit;
            padding: 0.7em;
        }

        textarea {
            min-height: 180px;
            resize: vertical;
        }

        article {
            margin-top: 1em;
            padding-top: 1em;
            border-top: 1px solid #ddd;
        }

        button {
            justify-self: start;
            min-width: 80px;
        }

        footer {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1em;
        }
    </style>
    <title>CMS</title>
</head>
<body>
    <header>
        <h1 class="site-logo"><a href="/">CMS</a></h1>
        <ul class="site-menus">
            <li><a href="?action=posts">posts</a></li>
            <li><a href="?action=write">write</a></li>
            <li><a href="#">register</a></li>
        </ul>
    </header>
    <div class="content">
        <?php if ($action === 'write'): ?>
            <h2>Write</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_post">
                <p>
                    <input type="text" name="title" placeholder="title">
                </p>
                <p>
                    <textarea type="body" name="body" placeholder="Content"></textarea>
                </p>

                <button type="submit">Save</button>
            </form>

        <?php elseif ($action === 'posts'): ?>
            <h2>Posts</h2>

            <?php 
            $posts = $db->query(" select * from posts order by id desc")->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (!$posts): ?>
                <p>No posts yet...</p>
            <?php endif; ?>

            <?php foreach ($posts as $post): ?>
                <article>
                    <h3><?= e($post['title']) ?></h3>
                    <p><?= nl2br(e($post['body'])) ?></p>
                    <small><?= e($post['created_at']) ?></small>
                </article>
            <?php endforeach; ?>

        <?php else: ?>
            <h1>WELCOME!</h1>
        <?php endif; ?>
    </div>
    <footer>
        Copyright &copy; <script>document.write(new Date().getFullYear());</script> JMK
    </footer>
    <script>
    </script>
</body>
</html>