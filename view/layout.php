<!doctype html>
<html>
    <head>
        <title>
            Tretyak Test Task
        </title>
        <meta charset="utf-8">

        <link rel="stylesheet" href="<?php echo url('/css/bootstrap.css'); ?>">
        <link rel="stylesheet" href="<?php echo url('/css/main.css'); ?>">
    </head>
    <body>

        <header>
            <nav class="navbar navbar-default">
              <div class="container">
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>
                  <a class="navbar-brand" href="<?php echo url(url_for('main')) ?>">Tretyak Test Task</a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                  <ul class="nav navbar-nav navbar-right">
                    <?php if ($current_user): ?>
                        <li><a href="<?php echo url(url_for('logout')) ?>">Sign Out</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo url(url_for('login')) ?>">Sign In</a></li>
                        <li><a href="<?php echo url(url_for('register')) ?>">Register</a></li>
                    <?php endif; ?>

                  </ul>
                </div>
              </div>
            </nav>

        </header>

        <div class="container">
            <div class="col-md-8 col-md-offset-2">
                <?php require($path); ?>
            </div>
        </div>

        <script src="<?php echo url('/scripts/jquery-1.12.1.js'); ?>"></script>
        <script src="<?php echo url('/scripts/bootstrap.js'); ?>"></script>

        <script src="<?php echo url('/scripts/pnotify.custom.min.js'); ?>"></script>
        <script src="<?php echo url('/scripts/app.js'); ?>"></script>
        <script src="<?php echo url('/scripts/users_liked.js'); ?>"></script>
        <script src="<?php echo url('/scripts/comments.js'); ?>"></script>

    </body>
</html>
