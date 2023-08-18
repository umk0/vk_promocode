<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Получение ID фото из ссылки страницы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

</head>

<body class="bg-dark">
    <div class="container pt-5">
        <input type="text" class="form-control" id="url" placeholder="Сюда ссылку страницу с фото">

        <div class="input-group mb-3 pt-3">
            <span class="input-group-text">Результат:</span>
            <input type="text" class="form-control" id="result" onclick="clipboard()" readonly>
            <button class="btn btn-success" onclick="clipboard()">Копировать в буффер обмена</button>
          </div>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"
    integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"
    integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous">
</script>

</html>
<script>
    function clipboard(){
        var copy = $("#result").val();
        var id_tmp = Date.now();
        $("body").append("<textarea id='cl" + id_tmp + "' readonly='readonly'></textarea>");
        var el = $("#cl" + id_tmp);
        el.val(copy).select();
        document.execCommand('copy')
        el.remove();
        alert("Успешно скопировано: "+copy)
    }

    $("#url").on('input', function () {
        try {
            var id_arr = $(this).val().split('photo-')[1].split('_');
            id_arr[1] = id_arr[1].split('%')[0];
            $("#result").val("-"+id_arr[0]+"_"+id_arr[1]);
        } catch (err) {
        }
    });
</script>