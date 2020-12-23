<?php
      $to = 'rogeriobenco@gmail.com';  //Change the email address by yours

      $header  = 'From: Teste Crontab <rogeriobenco@gmail.com>'. "\r\n";
      $header .= 'Reply-To:  Rogerio Benco <rogeriobenco@gmail.com>'. "\r\n";
      $header .= 'X-Mailer: PHP/' . phpversion();

      $message  = 'Teste do CRONTAB' . "\n\n";
      $message .= 'Email envidado em: ' . date('d/m/Y H:i:s') . "\n\n";
      $message .= 'PHP version: ' . phpversion();

      $mail = mail( $to, $subject , $message, $header ); 