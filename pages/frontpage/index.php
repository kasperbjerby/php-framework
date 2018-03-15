<h1><?=($me->isLoggedIn() ? "Welcome, ".$me->getUsername() : "Frontpage")?></h1>

<form method="post">
    <input type="text" name="username" placeholder="Username" <?=($me->isLoggedIn() ? "style='display:none'" : "required")?> />
    <input type="password" name="password" placeholder="Password" <?=($me->isLoggedIn() ? "style='display:none'" : "required")?> />
    <input type="submit" name="login" value="<?=($me->isLoggedIn() ? "Log out" : "Log in")?>" />
</form>