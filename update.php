<?php

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=products_crud', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = $_GET['id'] ?? 'null';

if (!$id) {
    header('Location: index.php');
    exit;
}

$statement = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$statement->bindValue(':id', $id);
$statement->execute();
$product = $statement->fetch(PDO::FETCH_ASSOC);

$errors = [];

$title = $product['title'];
$description = $product['description'];
$price = $product['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $title = $_POST['title'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  
  if (!$title) {
    $errors[] = 'A title is required';
  }
  if (!$price) {
    $errors[] = 'Price is required';
  }

  if (empty($errors)) {

    $image = $_FILES['image'] ?? null;
    $imagePath = $product['image'];

    if ($image && $image['tmp_name']) {

        if ($product['image']) {
            unlink($product['image']);
        }

      $imagePath = 'images/' . randomString(8) . '/' . $image['name'];
      mkdir(dirname($imagePath));

      move_uploaded_file($image['tmp_name'], $imagePath);
    }

    $statement = $pdo->prepare("UPDATE products SET title = :title, image = :image, description = :description, price = :price WHERE id = :id");
    $statement->bindValue(':title', $title);
    $statement->bindValue(':image', $imagePath);
    $statement->bindValue(':description', $description);
    $statement->bindValue(':price', $price);
    $statement->bindValue(':id', $id);
    $statement->execute();
    header('Location: index.php');
  }
}

  function randomString($n) {
    $stringOrigin = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < $n; $i++) {
      $index = rand(0, strlen($stringOrigin) - 1);
      $str .= $stringOrigin[$index];
    }

    return $str;
  }

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="./app.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">

    <title>Products CRUD</title>
  </head>
  <body>
    <a class="btn btn-sm btn-secondary" href="index.php">Back</a>
    <h1>Update Product <?php echo $product['title'] ?></h1>

    <?php if ($errors) : ?>
    <div class="alert alert-danger">
      <?php foreach($errors as $error) : ?>
        <div><?php echo $error ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
      
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Image</label>

            <?php if ($product['image']) : ?>
            <br>
            <img class="update-image" src="<?php echo $product['image'] ?>" alt="<?php echo $product['title'] ?>">
            <?php else :?>
            <br><br>
            <?php endif; ?>
            <input type="file" name="image">
        </div>
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo $title ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" value="<?php echo $description ?>"></textarea>
        </div>
        <div class="form-group">
            <label>Product Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $price ?>">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </body>
</html>