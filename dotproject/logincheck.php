<?php /* $Id$ */
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $ok = $AppUI->login( $username, $password );
    if (!$ok) {
        $AppUI->setMsg( 'Login Failed' );
        $AppUI->redirect();
    }
?>