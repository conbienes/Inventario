

<?php

function Imprimir(){

	
}
require __DIR__ . '/ticket/autoload.php'; //Nota: si renombraste la carpeta a algo diferente de "ticket" cambia el nombre en esta línea
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
 

$nombre_impresora = "Restaurante"; 
 
 
$connector = new WindowsPrintConnector($nombre_impresora);
$printer = new Printer($connector);
 
 
/*
	Vamos a imprimir un logotipo
	opcional. Recuerda que esto
	no funcionará en todas las
	impresoras
 
	Pequeña nota: Es recomendable que la imagen no sea
	transparente (aunque sea png hay que quitar el canal alfa)
	y que tenga una resolución baja. En mi caso
	la imagen que uso es de 250 x 250
*/
 
# Vamos a alinear al centro lo próximo que imprimamos
$printer->setJustification(Printer::JUSTIFY_CENTER);


/*
	Intentaremos cargar e imprimir
	el logo
	*/




$printer->text("TICKET ALMUERZO"  . "\n\n");
$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
switch ($empleado->id_div) {
	case '1':
		$printer->text("COMBIENES S.A"  . "\n");
		$printer->text("890.907.824" . "\n");
		break;
	case '2':
		$printer->text("Administracion Mayorca SAS" . "\n");
		$printer->text("901.344.877" . "\n");
			break;
	case '3':
		$printer->text("Mayor Diversion SAS" . "\n");
		$printer->text("901.120.630" . "\n");
				break;		
	default:
	$printer->text("No existe\n");
		break;
	}
	
	$printer->selectPrintMode();	
#La fecha también
$printer->text(date("d-m-Y H:i:s") . "\n\n");
 
 
/*
	Ahora vamos a imprimir los
	productos
*/

		$printer->setJustification(Printer::JUSTIFY_CENTER);
		   $printer->text('Valido solo en ');
		   $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		   $printer->text($restaurante->nombre . "\n\n");
		   $printer->selectPrintMode();			
		$printer->text($empleado->nombre ." ".$empleado->apellidos. "\n");
		$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		$printer->text($empleado->cedula ."\n");
		$printer->selectPrintMode();
		$printer->text("\n Firma:_____________________________ \n");
		
		$printer->text("\n Redimible solo el ");
		$printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		$printer->text(date("d-m-Y\n"));
		$printer->selectPrintMode();
	

 
/*
	Terminamos de imprimir
	los productos, ahora va el total
*/

 
/*
	Podemos poner también un pie de página
*/
$printer->text("\nRECUERDA QUE ESTE TICKET ".$ticket->id);

$printer->text("\n ES ÚNICO E INTRANSFERIBLE");
 

/*Alimentamos el papel 3 veces*/
$printer->feed(3);
 
/*
	Cortamos el papel. Si nuestra impresora
	no tiene soporte para ello, no generará
	ningún error
*/
$printer->cut();
 
/*
	Por medio de la impresora mandamos un pulso.
	Esto es útil cuando la tenemos conectada
	por ejemplo a un cajón
*/
$printer->pulse();
 
/*
	Para imprimir realmente, tenemos que "cerrar"
	la conexión con la impresora. Recuerda incluir esto al final de todos los archivos
*/

$printer->close();

return null;

?>