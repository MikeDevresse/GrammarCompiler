<?php
require './Compiler.php';

$formErrors = [];
if(isset($_POST['grammar']) and isset($_POST['input']) and isset($_POST['dictionary'])) {
    if(empty($_POST['grammar'])) {
        $formErrors['grammar'] = 'Une grammaire doit être entrée.';
    }
    if(empty($_POST['input'])) {
        $formErrors['input'] = 'Veuillez entrer un programme.';
    }
    if(empty($_POST['dictionary'])) {
        $formErrors['dictionary'] = 'Veuillez entrer un dictionnaire.';
    }
    if(empty($formErrors)) {
        $compiler = new Compiler($_POST['grammar'], $_POST['input'], $_POST['dictionary']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grammar compiler</title>

    <!-- FONTAWESOME -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css'
          integrity='sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w=='
          crossorigin='anonymous' referrerpolicy='no-referrer'/>

    <!-- BOOTSTRAP -->
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css'
          integrity='sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6' crossorigin='anonymous'>

    <!-- CUSTOM STYLES -->
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="bg-primary">
        <h3>Projet hébergé sur&nbsp;<a href="https://github.com/MikeDevresse/GrammarCompiler">github</a> <a href="#"><i class="fa fa-download"></i></a></h3>
    </header>
    <div class="container-fluid">
        <form method="post" class="h-100">
            <div class="row h-100">
                <div class="col-lg-4 d-lg-flex d-none grammar py-3">
                   <div class="form-group">
                       <label for="grammar">
                           Grammaire

                       </label>
                       <?php
                       echo '<textarea class="form-control '.(isset($formErrors['grammar'])?'is-invalid':'').'" name="grammar" id="grammar">'.
                           ((isset($_POST['grammar']) && !empty($_POST['grammar']))?$_POST['grammar']:file_get_contents('default_grammar.txt')).
                           '</textarea>';
                       if(isset($formErrors['grammar'])) echo '<div class="invalid-feedback">'.$formErrors['grammar'].'</div>';
                       ?>
                   </div>
                    <div class='form-group'>
                        <label for='dictionary'>
                            Dictionnaire
                        </label>
                        <?php
                        echo '<textarea class="form-control ' . (isset($formErrors['dictionary']) ? 'is-invalid' : '') . '" name="dictionary" id="dictionary">' .
                            ((isset($_POST['dictionary']) && !empty($_POST['dictionary'])) ? $_POST['dictionary'] : file_get_contents('default_dictionary.txt')) .
                            '</textarea>';
                        if (isset($formErrors['dictionary'])) echo '<div class="invalid-feedback">' . $formErrors['dictionary'] . '</div>';
                        ?>
                    </div>
                </div>
                <div class='col-lg-8 h-100 main'>
                    <div class='form-group'>
                        <label for='input'>
                            Entrée
                        </label>
                        <div class="input-group">
                            <?php
                            echo '<textarea class="form-control '.(isset($formErrors['input'])?'is-invalid':'').'" name="input" id="input">'.
                                ((isset($_POST['input']) && !empty($_POST['input']))?$_POST['input']:'').
                                '</textarea><button class="btn btn-primary">Compiler !</button>';
                            if(isset($formErrors['input'])) echo '<div class="invalid-feedback">'.$formErrors['input'].'</div>';
                            ?>

                        </div>
                    </div>
                    <div class='form-check form-switch'>
                        <input class='form-check-input' type='checkbox' id='interactif' name="interactif">
                        <label class='form-check-label' for='interactif'>Mode intéractif</label>
                    </div>
                    <?php if ($compiler !== null) { ?>
                    <div class="result">
                        <div class="inputs">
                            <?php
                            foreach ($compiler->getInput() as $k => $input) {
                                echo '<div class="input" id="input'.$k.'">'.$input.'</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
