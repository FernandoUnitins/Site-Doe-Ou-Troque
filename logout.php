<?php
session_start();
session_unset(); // limpa as variáveis da sessão
session_destroy(); // encerra a sessão

header("Location: login.html");
exit;
