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
                <div class="col-lg-4 d-lg-block d-none grammar h-100 py-3">
                   <div class="form-group">
                       <label for="grammar">
                           Grammaire

                       </label>
                       <textarea class="form-control" name="grammar" id="grammar"><?php
                            echo $_POST['grammar'] ?? file_get_contents('default_grammar.txt');
                       ?></textarea>
                   </div>
                </div>
                <div class='col-lg-8 h-100 main'>
                    <div class='form-group'>
                        <label for='input'>
                            Entrée
                        </label>
                        <div class="input-group">
                            <textarea class='form-control' name='input' id='input'><?php
                                echo $_POST['grammar'] ?? '';
                            ?></textarea>
                            <button class="btn btn-primary">Compiler !</button>
                        </div>
                    </div>
                    <div class='form-check form-switch'>
                        <input class='form-check-input' type='checkbox' id='interactif' name="interactif">
                        <label class='form-check-label' for='interactif'>Mode intéractif</label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
