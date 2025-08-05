<?php

include 'components/connect.php';
session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:user_login.php');
}

if (isset($_POST['delete'])) {
   $cart_id = $_POST['cart_id'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$cart_id]);
}

if (isset($_GET['delete_all'])) {
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
}

if (isset($_POST['update_qty'])) {
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'jumlah keranjang diperbarui';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Keranjang Shopping</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="products shopping-cart">

   <h3 class="heading">Keranjang Shopping</h3>

   <div class="box-container">
      <?php
      $grand_total = 0;
      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if ($select_cart->rowCount() > 0) {
         while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
      ?>
         <form action="" method="post" class="box">
            <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
            <a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
            <div class="name"><?= $fetch_cart['name']; ?></div>
            <div class="flex">
               <div class="price">$<?= $fetch_cart['price']; ?>/-</div>
               <input type="number" name="qty" class="qty" min="1" max="99"
                  onkeypress="if(this.value.length == 2) return false;" value="<?= $fetch_cart['quantity']; ?>">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
            </div>
            <div class="sub-total">sub total : <span>$<?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</span></div>
            <input type="submit" value="hapus item" onclick="return confirm('hapus ini dari keranjang?');" class="delete-btn" name="delete">
         </form>
      <?php
         $grand_total += $sub_total;
         }
      } else {
         echo '<p class="empty">keranjang Anda kosong</p>';
      }
      ?>
   </div>

   <div class="cart-total">
      <p>hasil akhir : <span>$<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">lanjutkan Belanja</a>
      <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="return confirm('hapus semua dari keranjang?');">hapus semua item</a>

      <!-- Tombol Bayar dengan Midtrans -->
      <?php if ($grand_total > 0): ?>
         <input type="hidden" id="user-name" value="<?= $_SESSION['user_name'] ?? 'Pelanggan'; ?>">
         <input type="hidden" id="user-email" value="<?= $_SESSION['user_email'] ?? 'user@example.com'; ?>">
         <input type="hidden" id="user-phone" value="08123456789">
         <input type="hidden" id="grand-total" value="<?= $grand_total; ?>">

         <button type="button" id="pay-button" class="btn">lanjutkan ke pembayaran</button>
      <?php else: ?>
         <button type="button" class="btn disabled" disabled>lanjutkan ke pembayaran</button>
      <?php endif; ?>
   </div>

</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>

<!-- MIDTRANS SCRIPT -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="Mid-client-97oXtSi2KplReiD-"></script>
<script>
document.getElementById('pay-button')?.addEventListener('click', function () {
    const name = document.getElementById('user-name')?.value;
    const email = document.getElementById('user-email')?.value;
    const phone = document.getElementById('user-phone')?.value;
    const total = document.getElementById('grand-total')?.value;

    fetch('midtrans.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            name: name,
            email: email,
            phone: phone,
            amount: total
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.token) {
            snap.pay(data.token, {
                onSuccess: function(result) {
                    alert("Pembayaran berhasil!");
                    window.location.href = 'thankyou.php';
                },
                onPending: function(result) {
                    alert("Menunggu pembayaran.");
                },
                onError: function(result) {
                    alert("Gagal: " + JSON.stringify(result));
                }
            });
        } else {
            alert("Gagal mendapatkan token Midtrans.");
            console.error(data);
        }
    });
});
</script>

</body>
</html>
