<?php
include 'global/config.php';
include 'global/conexion.php';
include 'carrito.php';
include 'templates/cabecera.php';
?>
<?php 
if($_POST) {
    $total = 0;
    $SID = session_id(); // guarda la clave de la session para evitar confucion con otro pedido
    $Correo = $_POST['email'];
    foreach($_SESSION['CARRITO'] as $indice=>$producto ) {
        $total = $total + ($producto['PRECIO']*$producto['CANTIDAD']);
    }
    $sentencia = $pdo->prepare("INSERT INTO `tblventas` (`ID`, `ClaveTransaccion`, `PaypalDatos`, `Fecha`, `Correo`, `Total`, `status`) VALUES (NULL, :ClaveTransaccion,'', NOW(), :Correo, :Total, 'pendiente');");

    $sentencia->bindParam(":ClaveTransaccion", $SID);
    $sentencia->bindParam(":Correo", $Correo);
    $sentencia->bindParam(":Total", $total);
    $sentencia->execute();
    $idVenta = $pdo->lastInsertId();

    foreach($_SESSION['CARRITO'] as $indice=>$producto ) {
    $sentencia = $pdo->prepare("INSERT INTO `tbldetalleventa` (`ID`, `IDVENTA`, `IDPRODUCTO`, `PRECIOUNITARIO`, `CANTIDAD`, `DESCARGADO`) VALUES (NULL, :IDVENTA, :IDPRODUCTO, :PRECIOUNITARIO, :CANTIDAD, '0');");

    $sentencia->bindParam(":IDVENTA",$idVenta);
    $sentencia->bindParam(":IDPRODUCTO",$producto['ID']);
    $sentencia->bindParam(":PRECIOUNITARIO",$producto['PRECIO']);
    $sentencia->bindParam(":CANTIDAD",$producto['CANTIDAD']);
    $sentencia->execute();
    }
    
    // echo "<h3>".$total."</h3>";
}
?>
<script src="https://www.paypalobjects.com/api/checkout.js"></script>

<style>
    
    /* Media query for mobile viewport */
    @media screen and (max-width: 400px) {
        #paypal-button-container {
            width: 100%;
        }
    }
    
    /* Media query for desktop viewport */
    @media screen and (min-width: 400px) {
        #paypal-button-container {
            width: 250px;
            display: inline-block;
        }
    }
    
</style>


<div class="jumbotron text-center">
    <h1 class="display-4 ">Paso final !!!</h1>
    <hr class="my-4">
    <p class="lead">Estas a punto de pagar con paypal el valor de:
        <h4>$<?php echo number_format($total,2); ?></h4>
        <div id="paypal-button-container"></div>
    </p>
    
    <p>Los productos podran ser descargados un vez se procese el pago.  
    </p>
    <strong> (Para aclaraciones: jairo.ortega@econectividad.com)</strong>
</div>

<!-- ********************************* paypal********************** -->




<script>

    paypal.Button.render({
        
        // Set your environment

        env: 'sandbox', // sandbox | production

        // Specify the style of the button

        style: {
            label: 'checkout',  // checkout | credit | pay | buynow | generic
            size:  'responsive', // small | medium | large | responsive
            shape: 'rect',   // pill | rect
            color: 'gold'   // gold | blue | silver | black
        },

        // PayPal Client IDs - replace with your own
        // Create a PayPal app: https://developer.paypal.com/developer/applications/create

        client: {
            sandbox:    'AZDxjDScFpQtjWTOUtWKbyN_bDt4OgqaF4eYXlewfBP4-8aqX3PiV8e1GWU6liB2CUXlkA59kJXE7M6R',
            production: '<insert production client id>'
        },

        // Wait for the PayPal button to be clicked

        payment: function(data, actions) {
            return actions.payment.create({
                payment: {
                    transactions: [
                        {
                            amount: { total: '<?php $total; ?>', currency: 'MXN' }
                        }
                    ]
                }
            });
        },

        // Wait for the payment to be authorized by the customer

        onAuthorize: function(data, actions) {
            return actions.payment.execute().then(function() {
                window.alert('Payment Complete!');
            });
        }
    
    }, '#paypal-button-container');

</script>
<!-- ************************************** fin paypal ********************************** -->
    

<?php include 'templates/pie.php'; ?>