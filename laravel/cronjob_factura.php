<?php
date_default_timezone_set('America/Guatemala');
class BD
{
	public $host;
	private $database;
	private $user;
	private $password;
	private $conexion;

	function __construct()
	{
        $envPath = __DIR__.DIRECTORY_SEPARATOR.'.env';
        $config = is_file($envPath) ? parse_ini_file($envPath, false, INI_SCANNER_RAW) : [];

        $this->host     = $config['DB_HOST'] ?? '127.0.0.1';
        $this->database = $config['DB_DATABASE'] ?? '';
        $this->user     = $config['DB_USERNAME'] ?? '';
        $this->password = $config['DB_PASSWORD'] ?? '';

		$this->conexion = null;
	}

	public function conectar(){
		
		$this->conexion = new mysqli($this->host, $this->user, $this->password, $this->database);
		
		/* verificar conexión */
		if (mysqli_connect_errno()) {
		    printf("Connect failed: %s\n", mysqli_connect_error());
		    exit();
		}

		$this->conexion->set_charset("utf8");

		return $this->conexion;
	}

	public function cerrar(){
		$this->conexion->close();
	}
}

class Funciones{

    public function GenerarFacturas()
    {   
        $this->insertLog('GenerarFacturas', 'Inicio proceso para generar facturas');
        $my_date = new DateTime();
        $my_date->modify('last day of this month');

        $date_expiration = $my_date->format('Y-m-d 23:59:59');
        $date_expiration_corta = $my_date->format('Y-m-d');

        $BD = new BD();
        $conexiondb = $BD->conectar();
        $sql = "SELECT client_product.id, (SELECT price FROM products WHERE id= product_id) as precio, client_product.payments_advancement FROM client_product LEFT OUTER JOIN invoices ON (client_product.id = invoices.client_product_id AND client_product.status = '1' AND ((invoices.date_expiration='$date_expiration' AND invoices.payments_advancement='0') || client_product.discounted_month = '$date_expiration_corta')) WHERE invoices.client_product_id IS NULL AND client_product.status = '1'";
        $sentencia = $conexiondb->prepare($sql);
    
        $sentencia->execute();
        $sentencia->bind_result($client_product_id, $precio, $payments_advancement);
        $total = 0;
        $sentencia->store_result();
        if($sentencia->num_rows>0){
            while ($sentencia->fetch()) {
                if($payments_advancement==0){
                    $this->insertInvoice($client_product_id, $precio, $date_expiration);
                    $total++;
                }else{
                    $this->disminuirMesesAdelanto($client_product_id, ($payments_advancement-1), $date_expiration_corta);
                }
            }
        }
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $this->insertLog('GenerarFacturas', 'Se generaron las facturas para el mes de '.$meses[date('n')-1].' - Total:'.$total);
        $BD->cerrar();
    }

    public function marcarFacturasComoVencidas()
    {   
        $this->insertLog('marcarFacturasComoVencidas', 'Inicio proceso de vencimiento de facturas');
        $BD = new BD();
        $conexiondb = $BD->conectar();
        $sentencia = $conexiondb->prepare("UPDATE `invoices` SET `status` = '-1', updated_at=? WHERE `date_expiration` <= ? AND status='0' ");
        $sentencia->bind_param('ss', $fecha_actual, $fecha_actual);
        $fecha_actual = date('Y-m-d H:i:s');
        if ($sentencia->execute()) {
            $result = true;		
        }else{
            $result = false;
        }
        $conexiondb->close();
        $this->insertLog('marcarFacturasComoVencidas', 'Se marcaron como vencidas las facturas no pagadas');
        return $result;
    }

    public function disminuirMesesAdelanto($contratacion, $payments_advancement, $discounted_month)
    {   
        $this->insertLog('disminuirMesesAdelanto', 'Inicio proceso de disminuir meses');
        $BD = new BD();
        $conexiondb = $BD->conectar();
        $sentencia = $conexiondb->prepare("UPDATE `client_product` SET `payments_advancement` = ?, discounted_month =?, updated_at=? WHERE id= ?");
        $sentencia->bind_param('issi', $payments_advancement, $discounted_month, $fecha_actual, $contratacion);
        $fecha_actual = date('Y-m-d H:i:s');
        if ($sentencia->execute()) {
            $result = true;     
        }else{
            $result = false;
        }
        $conexiondb->close();
        $this->insertLog('disminuirMesesAdelanto', 'Se cambio los meses a '.$payments_advancement.' de client_product '.$contratacion);
        return $result;
    }

    protected function insertInvoice($client_product_id, $price, $date_expiration)
    {
        $BD = new BD();
        $conexiondb = $BD->conectar();
        $sql = "INSERT INTO `invoices` ( `client_product_id`,  `price`, `date_expiration`, `created_at`, `updated_at`) VALUES 
        ( ?,  ?, ?, ?, ?)";
        $sentencia = $conexiondb->prepare($sql);
        $sentencia->bind_param('idsss', $client_product_id, $price, $date_expiration, $fecha, $fecha);
        $fecha = date('Y-m-d H:i:s');
        if($sentencia->execute()){
            $result = true;
        }else{
            $result = false;
        }

        $conexiondb->close();

        return $result;
    }

    protected function insertLog($tipo, $detalle)
    {
        $BD = new BD();
        $conexiondb = $BD->conectar();
        $sql = "INSERT INTO `logs` ( `tipo`,  `detalle`, `created_at`, `updated_at`) VALUES 
        (?,?,?,?)";
        $sentencia = $conexiondb->prepare($sql);
        $sentencia->bind_param('ssss', $tipo, $detalle, $fecha, $fecha);
        $fecha = date('Y-m-d H:i:s');
        if($sentencia->execute()){
            $result = true;
            echo $detalle."\n";
        }else{
            $result = false;
        }
        $conexiondb->close();
        return $result;
    }
}

if(isset($_GET['fecha_actual'])){
 echo json_encode(["fecha_actual"=>date('Y-m-d H:i:s')]);
 die();
}

$cron = new Funciones();

$cron->marcarFacturasComoVencidas();
$cron->GenerarFacturas();

?>
