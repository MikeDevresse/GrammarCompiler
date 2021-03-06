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
        try {
            $compiler = new Compiler($_POST['grammar'], $_POST['input'], $_POST['dictionary']);
            $compiler->compile();
        } catch (UnknownRuleException|WrongInputException $e) {
            $error = $e->getMessage();
        }
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
        <h3>Grammar compiler<a href="https://github.com/MikeDevresse/GrammarCompiler"><i class="fab fa-github"></i></a> <a href="https://github.com/MikeDevresse/GrammarCompiler/archive/refs/heads/main.zip"><i class="fa fa-download"></i></a></h3>
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
                <div class='col-lg-8 h-100 main d-flex flex-column'>
                    <?php if(isset($error)) { ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php } ?>
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
                        <input class='form-check-input' type='checkbox' id='interactif' name="interactif" <?php echo (isset($_POST['interactif']) and $_POST['interactif'] == 'on')? 'checked=""': '' ?>>
                        <label class='form-check-label' for='interactif'>Mode intéractif</label>
                    </div>
                    <div class="d-flex align-items-center  <?php echo (isset($_POST['interactif']) and $_POST['interactif'] == 'on')? '': 'd-none' ?>" id="div-automatic">
                        <div class='form-check form-switch'>
                            <input class='form-check-input' type='checkbox' id='automatic' name='automatic' <?php echo (isset($_POST['automatic']) and $_POST['automatic'] == 'on')? 'checked=""': '' ?>>
                            <label class='form-check-label' for='automatic'>Mode automatique</label>
                        </div>
                        <div class="form-group" style="margin-left: 10px">
                            <label for="delay">Délai</label>
                            <input class="form-control" type="number" id="delay" name="delay" value="<?php echo $_POST['delay']??'200'; ?>">
                        </div>
                    </div>

                    <?php if (isset($compiler)) { ?>
                    <hr/>
                    <div class="result d-flex flex-column">
                        <h3>Entrée</h3>
                        <div class="inputs">
                            <?php
                            foreach ($compiler->getInput() as $k => $input) {
                                echo '<div class="input" id="input'.$k.'">'.$input.'</div>';
                            }
                            ?>
                        </div>
                        <div class="output mt-2">
                            <div class="row">
                                <div class="col-lg-6">
                                    <h3>Sortie</h3>
                                    <div id="output"><?php echo implode(' ', $compiler->getOutput()); ?></div>
                                </div>
                                <div class="col-lg-6">
                                    <h4>Pile</h4>
                                    <div id="stack" class="stack">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                        <?php if(isset($_POST['interactif']) and $_POST['interactif'] == 'on'  and (!isset($_POST['automatic']) or $_POST['automatic'] != 'on')) {  ?>
                            <div class='actions'>
                                <button type="button" class="btn btn-outline-danger" id="previousButton">Précédent</button>
                                <button type='button' class='btn btn-outline-primary' id='nextButton'>Suivant</button>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>
    <script>

        document.getElementById('interactif').addEventListener('click', function() {
            if(this.checked)
                document.getElementById('div-automatic').classList.remove('d-none');
            else
                document.getElementById('div-automatic').classList.add('d-none');
        });
    </script>
    <?php if(isset($compiler) and isset($_POST['interactif']) and $_POST['interactif'] == 'on') {  ?>
        <script>

            let inputIndexes = <?php echo json_encode($compiler->getInputIndexes()) ?>;
            let outputs = <?php echo json_encode($compiler->getOutput()) ?>;
            let stacks = <?php echo json_encode($compiler->getStack()) ?>;

            let outputEl = document.getElementById('output');
            let stackEl = document.getElementById('stack');

            outputEl.innerText = '';

            var currentState = 0;
            setState(0);

            function setState(i) {
                outputEl.innerText = outputs.slice(0,i).join(' ');
                Array.from(document.getElementsByClassName('active')).forEach(function(el) {
                    el.classList.remove('active')
                });
                Array.from(document.getElementsByClassName('visited')).forEach(function(el) {
                    el.classList.remove('visited')
                });
                for(let j = 0 ; j < i ; j++) {
                    document.getElementById('input'+inputIndexes[j]).classList.add('visited');
                }
                document.getElementById('input'+inputIndexes[i]).classList.add('active');

                function setStacks() {
                    stackEl.innerHTML = '';
                    for(let j = 0 ; j<stacks[i].length ; j++) {
                        let div = document.createElement('div')
                        div.classList.add('stack-element')
                        div.innerText = stacks[i][j];
                        stackEl.append(div);
                    }
                }

                stackEl.style.height = 26*stacks[i].length + 'px';
                if(stacks[i].length >= stacks[currentState].length) {
                    setStacks();
                }
                else {
                    setTimeout(setStacks,300);
                }
                currentState = i;
            }

            <?php if(isset($_POST['automatic']) and $_POST['automatic'] == 'on') { ?>
            for(let i = 0 ; i<inputIndexes.length ; i++) {
                setTimeout(function() {
                    setState(i)
                },i*<?php echo $_POST['delay'] ?>);
            }
            <?php } else { ?>
                document.getElementById('nextButton').addEventListener('click',function() {
                    setState(Math.min(currentState+1,inputIndexes.length))
                });
                document.getElementById('previousButton').addEventListener('click',function() {
                    setState(Math.max(currentState-1,0))
                })
            <?php } ?>
        </script>
    <?php } ?>
</body>
</html>
