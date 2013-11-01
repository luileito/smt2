<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>SimpleTextEditor</title>
    <script type="text/javascript" src="editor.js"></script>
    <link rel="stylesheet" type="text/css" href="editor.css">
</head>
<body>
    <form action="save.php" method="post">
        <input type="hidden" name="id" value="<?=$_GET['id']?>" />
        <input type="hidden" name="login" value="<?=$_GET['login']?>" />
        <input type="hidden" name="time" value="<?=$_GET['time']?>" />
        <textarea id="data" name="data" cols="60" rows="10">
        <?php
        require '../../../../config.php';
        $hn = new Hypernote($_GET['id'], $_GET['login'], $_GET['time']);
        $notes = $hn->getData();
        // when login and time are both passed, there's only one hypernote
        if ($notes) {
          echo $notes[0]['txt'];
          $action = "reedit";
        } else {
          $action = "create";
        }
        ?>
        </textarea>
        <input type="hidden" name="action" value="<?=$action?>" />
        <input type="submit" value="Submit Hypernote" onclick="send();">
        <script type="text/javascript">
        var ste = new SimpleTextEditor("data", "ste");
        ste.init();
        function send() {
          ste.submit(); // puts editor content in "ed"
        }
        </script>        
    </form>
</body>
</html>
