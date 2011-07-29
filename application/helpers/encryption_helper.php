<?php
/*---------------------------------------------------------------------------*/
// encrypt_command
//
// sends data to encrypt to stdin, returns result code
//
// expects a gpg command like
// /usr/bin/gpg --homedir /home/www-data/.gnupg --armor --batch -e -r 'CitrusDB'
//
/*---------------------------------------------------------------------------*/
function encrypt_command ($gpg_command, $data)
{
  $descriptors = array(
		       0 => array("pipe", "r"), //stdin
		       1 => array("pipe", "w"), //stdout
		       2 => array("pipe", "w"), //stderr
		       );

  $process = proc_open($gpg_command, $descriptors, $pipes);

  if (is_resource($process)) {
    // send data to encrypt to stdin
    fwrite($pipes[0], $data);
    fclose($pipes[0]);

    // read stdout
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // read stderr
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_code = proc_close($process);

    $return_value = trim($stdout, "\n");
    //echo "$stdout";

    if (strlen($return_value) < 1) {
      $return_value = "error: $stderr";
    }

  }

  return $return_value;

}

/*---------------------------------------------------------------------------*/
// decrypt_command
//
// sends passphrase to stdin, returns decrypted data
//
// expects a gpg command like:
// /usr/bin/gpg --homedir /home/www-data/.gnupg --passphrase-fd 0 --yes --no-tty --skip-verify --decrypt file.gpg
//
/*---------------------------------------------------------------------------*/
function decrypt_command ($gpg_command, $passphrase)
{
  
  $descriptors = array(
		       0 => array("pipe", "r"), //stdin
		       1 => array("pipe", "w"), //stdout
		       2 => array("pipe", "w"), //stderr
		       );

  $process = proc_open($gpg_command, $descriptors, $pipes);

  if (is_resource($process)) {
    // send passphrase to stdin
    fwrite($pipes[0], $passphrase);
    fclose($pipes[0]);

    // read stdout
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // read stderr
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_code = proc_close($process);

    $return_value = trim($stdout, "\n");
    //echo "$stdout";

    if (strlen($return_value) < 1) {
      $return_value = "error: $stderr";
    }

  }

  return $return_value;
}

/*---------------------------------------------------------------------------*/
// sign_command
//
// sends passphrase to stdin for a file signature, returns nothing on success
//
// expects a gpg command like:
// /usr/bin/gpg --homedir /home/www-data/.gnupg --passphrase-fd 0 --yes --no-tty --clearsign file.tmp
//
/*---------------------------------------------------------------------------*/
function sign_command ($gpg_command, $passphrase)
{

  $descriptors = array(
		       0 => array("pipe", "r"), //stdin
		       1 => array("pipe", "w"), //stdout
		       2 => array("pipe", "w"), //stderr
		       );

  $process = proc_open($gpg_command, $descriptors, $pipes);

  if (is_resource($process)) {
    // send passphrase to stdin
    fwrite($pipes[0], $passphrase);
    fclose($pipes[0]);

    // read stdout
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // read stderr
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);       

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_code = proc_close($process);

    $return_value = trim($stdout, "\n");
    //echo "$stdout";

    if (strlen($stderr) > 0) {
      $return_value = "error: $stderr";
    }

  }

  return $return_value;
}
?>