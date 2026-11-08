<!DOCTYPE html>
<html lang="en">
<head>
  <title>PDF orden</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=2">
    <style>
        body{
            font-size: 10px
        }

        p{
            margin: 0px;
        }
    </style>
</head>
<body>
<?php $data = empresaConfig() ?>
  <table>
      <thead>
          <tr>
              <th>
                <p>{{ $data->nombre_empresa }}</p>
                <p>{{ $data->direccion_empresa }}</p>
                <p>{{ $data->nit_empresa }}</p>
              </th>
            </tr>
      </thead>
  </table>

</body>
</html>
