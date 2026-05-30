<?php
session_start();

$db = new PDO('sqlite:' . __DIR__ . '/db.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("
    create table if not exists posts ( 
        id integer primary key autoincrement,
        title text not null,
        body text not null,
        created_at text not null default current_timestamp,
        updated_at text not null default current_timestamp
    )
");

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$action = $_GET['action'] ?? 'posts';
$id = (int) ($_GET['id'] ?? 0);

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] === 'delete_post')) {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $db->prepare("delete from posts where id = :id");
        $stmt->execute([':id' => $id]);
    }

    header('Location: ?action=posts');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] === 'update_post')) {
    $id = (int) ($_POST['id'] ?? 0);
    $title = (string) ($_POST['title'] ?? '');
    $body = (string) ($_POST['body'] ?? '');

    if ($id > 0 && $title !== '' && $body !== '') {
        $stmt = $db->prepare("
            update posts 
            set title = :title, 
                body = :body,
                updated_at = current_timestamp
            where id = :id
        ");
        $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':body' => $body]);
    }

    header("Location: ?action=show&id=" . $id);
    exit;
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
        @import url('https://googleapis.com');

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
            font-family: 'Roboto', sans-serif;
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
            display: inline-block;
            padding: 0.3em 0.4em;
            border-radius: 5px;
        }

        a:hover {
            background-color: #eee;
            transition-duration: 0.2s;
        }

        header {
            padding: 0.5em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid black;
        }

        header .site-menus a {
            font-size: large;
        }

        .content {
            padding: 1em;
            flex: 1;
        }

        .post-container {
            max-width: 720px;
            margin: 3em auto;
            padding: 0 1em;
            display: block;
        }

        .post-container h2 {
            font-size: 2rem;
            line-height: 1.2;
            margin-bottom: 0.4em;
        }

        .post-container small {
            display: block;
            color: #777;
            margin-bottom: 2em;
        }

        .post-container p {
            font-size: 1.05rem;
            line-height: 1.8;
            margin-bottom: 1.5em;
            white-space: pre-wrap;
        }

        .post-actions {
            display: flex;
            align-items: center;
            gap: 0.75em;
            margin-top: 2em;
        }

        .post-form {
            max-width: 720px;
            margin: 3em auto;
            padding: 0 1em;
        }

        .post-form h2 {
            font-size: 2rem;
            line-height: 1.2;
            margin-bottom: 1em;
        }

        .post-form form {
            max-width: none;
        }

        .post-form input,
        .post-form textarea {
            width: 100%;
        }

        .post-form textarea {
            min-height: 60vh;
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
            border-top: 1px solid black;
        }
    </style>
    <title>CMS</title>
</head>
<body>
    <header>
        <h1 class="site-logo"><a href="/">CMS</a></h1>
        <ul class="site-menus">
            <li><a href="?action=posts">Posts</a></li>
            <li><a href="?action=write">Write</a></li>
        </ul>
    </header>
    <div class="content">
        <!--Post writing page-->
        <?php if ($action === 'write'): ?>
            <h2>Write</h2>
            <div class="post-form">
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
            </div>

        <!--Post list page-->
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
                    <a href="?action=show&id=<?= e($post['id']) ?>">
                        <h3><?= e($post['title']) ?></h3>
                    </a>
                    <small>
                        Created at: <?= e($post['created_at']) ?>
                        <?php if ($post['created_at'] !== $post['updated_at']): ?>
                            | Updated at: <?= e($post['updated_at']) ?>
                        <?php endif; ?>
                    </small>
                </article>
            <?php endforeach; ?>
        
        <!--Post detail page-->
        <?php elseif ($action === 'show' && $id > 0): ?>
            <?php
            $stmt = $db->prepare("select * from posts where id = :id");
            $stmt->execute([':id' => $id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>

            <?php if (!$post): ?>
                <h2>Post not found</h2>
                <p><a href="?action=show">go back</a></p>
            <?php else: ?>
                <div class="post-container">
                    <h2><?= e($post['title']) ?></h2>
                    <p><?= e($post['body']) ?></p>
                    <small>
                        Created at: <?= e($post['created_at']) ?>
                        <?php if ($post['created_at'] !== $post['updated_at']): ?>
                            | Updated at: <?= e($post['updated_at']) ?>
                        <?php endif; ?>
                    </small>
                    <div class="post-actions">
                        <a href="?action=posts">Go back</a>
                        <a href="?action=update&id=<?= e($post['id']) ?>">Update</a>
                        <button
                            class="post-delete-btn"
                            data-id="<?= e($post['id']) ?>"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            <?php endif; ?>

        <!--Post update page-->
        <?php elseif ($action === 'update' && $id > 0): ?>
            <?php
            $stmt = $db->prepare("select * from posts where id = :id");
            $stmt->execute([':id' => $id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <?php if (!$post): ?>
                <h2>Post not found</h2>
                <p><a href="?action=posts">go back</a></p>
            <?php else: ?>
                <div class="post-form">
                    <h2>Update post</h2>
                    <form method="post">
                        <input type="hidden" name="action" value="update_post">
                        <input type="hidden" name="id" value="<?= e($post['id']) ?>">
                        <p>
                            <input type="text" name="title" value="<?= e($post['title']) ?>">
                        </p>
                        <p>
                            <textarea name="body"><?= e($post['body']) ?></textarea>
                        </p>
                        <button type="submit">Save</button>
                    </form>
                    <div class="post-actions">
                        <a href="?action=show&id=<?= e($post['id']) ?>">Cancel</a>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
    <footer>
        Copyright &copy; <script>document.write(new Date().getFullYear());</script> JMK
    </footer>
    <script>
        $(document).ready(() => {
            $(".post-delete-btn").on("click", function () {
                if (!confirm("Delete this post?")) return;
                $.post("/", {
                    action: "delete_post",
                    id: $(this).data("id"),
                }).done(() => {
                    location.href = "?action=posts";
                })
            });
        });
    </script>
</body>
</html>
