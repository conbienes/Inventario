<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Doc\FileController;
use App\Http\Controllers\Doc\ComercialController;
use App\Http\Controllers\Doc\ExpInmuebleController;
use App\Http\Controllers\Seguridad\LoginController;
use App\Http\Controllers\Inventario\Inicio\HomeInventarioController;
use App\Http\Controllers\Inicio\InicioController;
use App\Http\Controllers\Registrar\RegEmpleadoController;
use App\Http\Controllers\Informes\InformesController;
use App\Http\Controllers\Inventario\Unidades\UnidadMedidaController;
use App\Http\Controllers\Inventario\Almacen\AlmacenController;
use App\Http\Controllers\Inventario\Categoria\CategoriaController;
use App\Http\Controllers\Inventario\Producto\ProductoController;
use App\Http\Controllers\Inventario\Proveedor\ProveedorController;
use App\Http\Controllers\Inventario\InventarioController;
use App\Http\Controllers\Inventario\Ajax\AjaxController;
use App\Http\Controllers\Inventario\EditInven\EditInvenController;
use App\Http\Controllers\Inventario\Movimientos\MovimientosController;
use App\Http\Controllers\Inventario\SolicProduc\SolicProducController;
use App\Http\Controllers\Inventario\EntradaAlmacen\EntradaAlmacenController;
use App\Http\Controllers\Inventario\Arqueo\ArqueoController;
use App\Http\Controllers\Inventario\Alertastock\AlertastockController;
use App\Http\Controllers\BonoRegalo\BonoRegaloController;
use App\Http\Controllers\BonoRegalo\FacturasBRController;
use App\Http\Controllers\BonoRegalo\ClientesBRController;
use App\Http\Controllers\BonoRegalo\TarjetasBRController;
use App\Http\Controllers\BonoRegalo\ReporteVentasController;
use App\Http\Controllers\BC\BcSyncController;

// Rutas de autenticación (sin registro)
Auth::routes(['register' => false]);

// Rutas públicas
Route::get('/', [InformesController::class, 'index'])->name('acceso');
Route::post('/acceso', [LoginController::class, 'login'])->name('login_post');
Route::get('/Cerrar', [LoginController::class, 'logout'])->name('Cerrar');

// Rutas protegidas con autenticación
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [InicioController::class, 'index'])->name('Inicio');

    Route::get('/cambiar_contraseña', [RegEmpleadoController::class, 'contrasena'])->name('contrasena');

    Route::put('/actualizar_contraseña/{id}', [RegEmpleadoController::class, 'actcontrasena'])->name('actualizar_contrasena');
});

Route::middleware(['auth', 'nivel:current,1'])->group(function () {
    // Rutas de gestión de empleados
    Route::get('/Reg_Empleado', [RegEmpleadoController::class, 'index'])->name('Reg_Empleado');

    Route::post('/Reg_Empleado', [RegEmpleadoController::class, 'guardar'])->name('crear_Empleado');

    Route::get('/Reg_Empleado/consultar_Empleado', [RegEmpleadoController::class, 'consultar'])->name('consultar_Empleado');

    Route::get('/Reg_Empleado/{id}/Editar', [RegEmpleadoController::class, 'editar'])->name('editar_empleado');

    Route::put('/Reg_Empleado/{id}', [RegEmpleadoController::class, 'actualizar'])->name('actualizar_empleado');
});

Route::middleware(['auth', 'nivel:current,1|2|6,all,in:1|2'])->group(function () {
    // Rutas para cambio de empresa
    Route::get('/CambiarEmpresa', [RegEmpleadoController::class, 'CambiarEmpresa'])->name('CambiarEmpresa');

    Route::post('/ActualizarEmpresa', [RegEmpleadoController::class, 'ActualizarEmpresa'])->name('ActualizarEmpresa');
});

Route::group(
    [
        'prefix' => 'Informes',
        'middleware' => ['auth', 'nivel:1,1|2'], // Nota: middlewares como array
    ],
    function () {
        Route::post('/exportar/Arqueo', [InformesController::class, 'ExportarArqueo'])->name('Exportar_Arqueo');
        Route::post('/exportar/Alertas', [InformesController::class, 'ExportarAlertas'])->name('Exportar_Alertas');
        Route::get('/exportar/Solicitudes', [InformesController::class, 'Solicitudes'])->name('Exportar_Solicitudes');
        Route::get('/exportar/EntradasAlmacen', [InformesController::class, 'EntradasAlmacen'])->name('Exportar_EntradasAlmacen');
    },
);

// Grupo para Unidades de Medida
Route::prefix('UndMedid')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/', [UnidadMedidaController::class, 'consultar'])->name('UnidMedida');
        Route::get('/Crear', [UnidadMedidaController::class, 'Crear'])->name('Crear_Medida');
        Route::post('/Crear', [UnidadMedidaController::class, 'Guardar'])->name('Guardar_Medida');
        Route::get('/{id}/Editar', [UnidadMedidaController::class, 'editar'])->name('editar_UnidMedida');
        Route::post('/Actualizar', [UnidadMedidaController::class, 'Actualizar'])->name('actualizar_Medida');
    });

// Grupo para Almacén
Route::prefix('Almacen')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/', [AlmacenController::class, 'consultar'])->name('Almacen');
        Route::get('/Crear', [AlmacenController::class, 'Crear'])->name('Crear_Almacen');
        Route::post('/Crear', [AlmacenController::class, 'Guardar'])->name('Guardar_Almacen');
        Route::get('/{id}/Editar', [AlmacenController::class, 'editar'])->name('editar_Almacen');
        Route::post('/Actualizar', [AlmacenController::class, 'Actualizar'])->name('actualizar_Almacen');
    });

// Grupo para Categorías
Route::prefix('Categoria')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/', [CategoriaController::class, 'consultar'])->name('Categoria');
        Route::get('/Crear', [CategoriaController::class, 'Crear'])->name('Crear_Categoria');
        Route::post('/Crear', [CategoriaController::class, 'Guardar'])->name('Guardar_Categoria');
        Route::get('/{id}/Editar', [CategoriaController::class, 'editar'])->name('editar_Categoria');
        Route::post('/Actualizar', [CategoriaController::class, 'Actualizar'])->name('actualizar_Categoria');
    });

// Grupo para Productos
Route::prefix('Producto')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/', [ProductoController::class, 'consultar'])->name('Producto');
        Route::get('/Crear', [ProductoController::class, 'Crear'])->name('Crear_Producto');
        Route::post('/Crear', [ProductoController::class, 'Guardar'])->name('Guardar_Producto');
        Route::get('/{id}/Editar', [ProductoController::class, 'editar'])->name('editar_Producto');
        Route::post('/Actualizar', [ProductoController::class, 'Actualizar'])->name('actualizar_Producto');
    });

// Grupo para Proveedores
Route::prefix('Proveedor')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/', [ProveedorController::class, 'consultar'])->name('Proveedor');
        Route::get('/Crear', [ProveedorController::class, 'Crear'])->name('Crear_Proveedor');
        Route::post('/Crear', [ProveedorController::class, 'Guardar'])->name('Guardar_Proveedor');
        Route::get('/{id}/Editar', [ProveedorController::class, 'editar'])->name('editar_Proveedor');
        Route::post('/Actualizar', [ProveedorController::class, 'Actualizar'])->name('actualizar_Proveedor');
    });

// Grupo para Inventario
Route::prefix('Inventario')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/', [InventarioController::class, 'Inicio'])->name('Inventario');
        Route::get('/{id}/Consultar', [InventarioController::class, 'consultar'])->name('Consultar_Inventario');
        Route::get('/{id}/Producto', [InventarioController::class, 'Producto'])->name('Consultar_Producto');
        Route::get('/Crear', [InventarioController::class, 'Crear'])->name('Crear_Inventario');
        Route::post('/Crear', [InventarioController::class, 'Guardar'])->name('Guardar_Inventario');
        Route::get('/{id}/Editar', [InventarioController::class, 'editar'])->name('editar_Inventario');
        Route::post('/Actualizar', [InventarioController::class, 'Actualizar'])->name('actualizar_Inventario');
    });

// Rutas AJAX
Route::prefix('Ajax')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/Division', [AjaxController::class, 'Division']);
        Route::get('/Almacen', [AjaxController::class, 'Almacen']);
        Route::get('/Categoria', [AjaxController::class, 'Categoria']);
        Route::get('/Empleado', [AjaxController::class, 'Empleado']);
        Route::get('/Empleados', [AjaxController::class, 'Empleados']);
        Route::get('/Area', [AjaxController::class, 'Area']);
        Route::get('/topografica', [AjaxController::class, 'topografica']);
        Route::get('/Proveedor', [AjaxController::class, 'Proveedor']);
        Route::get('/Inventario', [AjaxController::class, 'Inventario']);
        Route::get('/Inventario1', [AjaxController::class, 'Inventario1']);
        Route::get('/Inventario2', [AjaxController::class, 'Inventario2']);
        Route::get('/Inventario3', [AjaxController::class, 'Inventario3']);
        Route::get('/Producto', [AjaxController::class, 'Producto']);
        Route::get('/Correos', [AjaxController::class, 'Correos']);
        Route::get('/buscarClientesBR', [AjaxController::class, 'ClienteBonoR'])->name('buscarClientesBR');
        Route::get('/buscarTarjetasBR', [AjaxController::class, 'buscarTarjetasBR'])->name('buscarTarjetasBR');
    });

// Rutas para Edición de Inventario
Route::prefix('EditInven')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/{Id}/Editar', [EditInvenController::class, 'editar'])->name('editar_EditInven');
        Route::get('/{Id}/EditarInventario', [EditInvenController::class, 'EditarInventario'])->name('EditarInventario');
        Route::post('/Actualizar', [EditInvenController::class, 'Actualizar'])->name('actualizar_EditInven');
        Route::post('/ActualizarInventario', [EditInvenController::class, 'ActualizarInventario'])->name('ActualizarInventario');
    });

// Rutas para Movimientos
Route::prefix('Movimientos')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/', [MovimientosController::class, 'consultar'])->name('Movimientos');
        Route::get('/Crear', [MovimientosController::class, 'Crear'])->name('Crear_Movimientos');
        Route::post('/CrearMovimi', [MovimientosController::class, 'Guardar'])->name('Guardar_Movimientos');
        Route::get('/{id}/Editar', [MovimientosController::class, 'editar'])->name('editar_Movimientos');
        Route::post('/Actualizar', [MovimientosController::class, 'Actualizar'])->name('actualizar_Movimientos');
    });

// Rutas para Solicitud de Productos
Route::prefix('SolicProduc')
    ->middleware(['auth', 'nivel:1,1|2|3'])
    ->group(function () {
        Route::get('/', [SolicProducController::class, 'Crear'])->name('Crear_SolicProduc');
        Route::post('/Crear', [SolicProducController::class, 'IngProducto'])->name('IngProducto');
        Route::get('/{id}/Editar', [SolicProducController::class, 'editar'])->name('editar_SolicProduc');
        Route::post('/Actualizar', [SolicProducController::class, 'Guardar'])->name('actualizar_SolicProduc');
    });

// Rutas para Entrada a Almacén
Route::prefix('EntAlmacen')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/', [EntradaAlmacenController::class, 'Inicio'])->name('EntAlmacen');
        Route::get('/{id}/Crear', [EntradaAlmacenController::class, 'Crear'])->name('Crear_EntAlmacen');
        Route::post('/AñadirFactura', [EntradaAlmacenController::class, 'AñadirFactura'])->name('AñadirFactura');
        Route::post('/Crear', [EntradaAlmacenController::class, 'CrearFactura'])->name('Crear_Factura');
        Route::post('/NotPersonal', [EntradaAlmacenController::class, 'NotificarPersonal'])->name('NotificarPersonal');
        Route::get('/{id}/SolicAproba', [EntradaAlmacenController::class, 'SolicAproba'])->name('SolicAprobadasFacturas');
        Route::get('/{id}/FVistaAprobadas', [EntradaAlmacenController::class, 'FVistaAprobadas'])->name('FVistaAprobadas');
        Route::get('/{id}/EditarSolicitud', [EntradaAlmacenController::class, 'EditarSolicitud'])->name('EditarSolicitud');
        Route::post('/ActualizarPedido', [EntradaAlmacenController::class, 'ActualizarPedido'])->name('ActualizarPedido');
        Route::get('/{id}/SolicPend', [EntradaAlmacenController::class, 'SolicPend'])->name('SolicPendFacturas');
        Route::get('/{id}/Vista', [EntradaAlmacenController::class, 'Vista'])->name('Vista_EntAlmacen');
        Route::get('/{id}/{div}/AprobarFactura', [EntradaAlmacenController::class, 'AprobarFactura'])->name('AprobarFactura');
        Route::post('/CalificarPost', [EntradaAlmacenController::class, 'Calificar'])->name('CalificarPost');
        Route::get('/Calificar', [EntradaAlmacenController::class, 'Calificar'])->name('Calificar');
        Route::post('/AprobarFactura', [EntradaAlmacenController::class, 'AprobarItems'])->name('AprobarItems');
    });

// Rutas para Arqueo
Route::prefix('Arqueo')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/{id}/Empresa', [ArqueoController::class, 'Consultar'])->name('Consultar_Arqueo_Empresa');
        Route::post('/import', [ArqueoController::class, 'import'])->name('import');
        Route::get('/', [ArqueoController::class, 'Inicio'])->name('In_Arqueo');
    });

// Rutas para Alertas de Stock
Route::prefix('Alertastock')
    ->middleware(['auth', 'nivel:1,1|2'])
    ->group(function () {
        Route::get('/{id}', [AlertastockController::class, 'AlertaStock'])->name('AlertaStock');
        Route::get('/', [AlertastockController::class, 'InicioAlertaStock'])->name('InicioAlertaStock');
    });

Route::middleware(['auth', 'nivel:1'])
    ->prefix('/')
    ->group(function () {
        Route::get('homeInventario', [HomeInventarioController::class, 'Inicio'])->name('InicioInventario');
        Route::get('Filtro', [HomeInventarioController::class, 'Filtro'])->name('Filtro');
        Route::get('Pendientes', [HomeInventarioController::class, 'Pendientes'])->name('Pendientes');
        Route::get('{id}/VistaPendientes', [HomeInventarioController::class, 'Vista'])->name('VistaPendientes');
        Route::get('Aprobadas', [HomeInventarioController::class, 'Aprobadas'])->name('Aprobadas');
        Route::get('TotalAprobadas', [HomeInventarioController::class, 'TotalAprobadas'])->name('TotalAprobadas');
        Route::post('TotalAprobadas', [HomeInventarioController::class, 'TotalAprobadas'])->name('TotalAprobadasP');
        Route::get('{id}/VistaAprobados', [HomeInventarioController::class, 'VistaAprobadas'])->name('VistaAprobados');
        Route::get('Solicitudes', [HomeInventarioController::class, 'Solicitudes'])->name('Solicitudes');
        Route::get('{id}/Cumplir', [HomeInventarioController::class, 'Cumplir'])->name('Cumplir');
        Route::post('AprobarSolicitud', [HomeInventarioController::class, 'AprobarSolicitud'])->name('AprobarSolicitud');
    });
//////////////////////////////////////////////////////////////Bono regalo/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Bono regalo/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Bono regalo/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Bono regalo/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Bono regalo/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Bono regalo/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Bono regalo/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Bono regalo/////////////////////////////////////////////////////

//Route::get('/Prueba', [BcSyncController::class, 'companies']);
// Desactivada: era pública (sin auth) y creaba asientos reales en BC
//Route::match(['get', 'post'], '/bc', [BcSyncController::class, 'testEjemplo']);

//Route::get('/bc/custom/cliente', [BcSyncController::class, 'crearClienteExt']);

Route::middleware(['auth', 'nivel:3,1|2|3,any']) // Middleware personalizado
    ->prefix('/BonoRegalo')
    ->name('BonoRegalo.')
    ->group(function () {
        Route::post('Guardar', [BonoRegaloController::class, 'store'])->name('store');
        Route::get('{id}/Editar', [BonoRegaloController::class, 'edit'])->name('edit');
        Route::put('{id}/Actualizar', [BonoRegaloController::class, 'update'])->name('update');
        Route::get('/ventas/hoy', [BonoRegaloController::class, 'ventasHoy'])->name('ventasHoy');
        // Rutas adicionales específicas
        Route::post('Validar', [BonoRegaloController::class, 'validarBono'])->name('validar');
        Route::get('Reporte', [BonoRegaloController::class, 'generarReporte'])->name('reporte');

        // Clientes Bono Regalo
        Route::get('Cliente', [ClientesBRController::class, 'Index'])->name('IndexCliente');
        Route::get('BuscarCliente', [ClientesBRController::class, 'BuscarCliente'])->name('BuscarCliente');
        Route::post('Cliente', [ClientesBRController::class, 'store'])->name('crearCliente');
        Route::get('Cliente/{id}/Editar', [ClientesBRController::class, 'edit'])->name('editarCliente');
        Route::put('Cliente/{id}/Actualizar', [ClientesBRController::class, 'update'])->name('actualizarCliente');
        Route::delete('Cliente/{id}', [ClientesBRController::class, 'destroy'])->name('DestroyCliente');

        Route::get('Facturas', [FacturasBRController::class, 'Index'])->name('IndexFacturas');
        Route::post('Facturas', [FacturasBRController::class, 'store'])->name('crearFactura');
        Route::get('/buscarClientes', [FacturasBRController::class, 'buscar'])->name('buscarClientes');
        Route::get('/InformesTarjetas', [FacturasBRController::class, 'informesTarjetas'])->name('informesTarjetas');
        Route::get('/InformesTarjetas/exportar', [FacturasBRController::class, 'exportarExcel'])->name('exportarFacturas');

        Route::get('factura/{id}/detalle', [BonoRegaloController::class, 'detalleFactura'])->name('detalleFactura'); // AJAX -> JSON/HTML
        Route::get('factura/{id}/print', [BonoRegaloController::class, 'printFactura'])->name('printFactura'); // vista térmica
        // Opcional: endpoint POST para impresión por servidor si usas escpos-php:
        Route::post('factura/{id}/print-server', [BonoRegaloController::class, 'printFacturaServer'])->name('printFacturaServer');
        Route::get('resumen', [BonoRegaloController::class, 'resumenFactura'])->name('printResumen');
    });

Route::middleware(['auth', 'nivel:3']) // Middleware personalizado
    ->prefix('/BonoRegalo')
    ->name('BonoRegalo.')
    ->group(function () {
        Route::get('Inicio', [BonoRegaloController::class, 'index'])->name('inicio');
        Route::get('/bono-regalo', [BonoRegaloController::class, 'Reportes'])->name('ReporteVentas');
        Route::get('/bono-regalo/ventas/resumen', [BonoRegaloController::class, 'resumen'])->name('ResumenVentas');

    });
    Route::middleware(['auth', 'nivel:3,1|2|5,any'])
    ->prefix('/BonoRegalo')
    ->name('BonoRegalo.')
    ->group(function () {
        // ===== TARJETAS BONO REGALO =====
        Route::get('Tarjeta', [TarjetasBRController::class, 'index'])->name('IndexTarjeta'); // ✅ Cambiado a minúscula
        Route::get('BuscarTarjeta', [TarjetasBRController::class, 'BuscarTarjeta'])->name('BuscarTarjeta');

        Route::post('Tarjeta', [TarjetasBRController::class, 'store'])->name('crearTarjeta');
        Route::get('Tarjeta/{id}/Editar', [TarjetasBRController::class, 'edit'])->name('editarTarjeta');
        Route::put('Tarjeta/{id}/Actualizar', [TarjetasBRController::class, 'update'])->name('actualizarTarjeta');
        Route::delete('Tarjeta/{id}', [TarjetasBRController::class, 'destroy'])->name('DestroyTarjeta');

        // ===== IMPORTACIÓN Y EXPORTACIÓN =====
        Route::post('tarjetas/importar', [TarjetasBRController::class, 'importarTarjetas'])->name('ImportarTarjeta');
        Route::get('tarjetas/plantilla', [TarjetasBRController::class, 'plantillaTarjetas'])->name('PlantillaTarjetas');
        Route::get('Informes/exportar-tarjetas', [TarjetasBRController::class, 'exportarTarjetas'])->name('ExportarTarjeta');

        // ===== INFORMES Y REPORTES =====
        Route::get('Informes', [TarjetasBRController::class, 'Informes'])->name('Informes');
        Route::get('Contabilidad', [ReporteVentasController::class, 'Contabilidad'])->name('Contabilidad');

        // ===== SERVICIOS EXTERNOS =====
        Route::post('CargarBC', [ReporteVentasController::class, 'CargarBC'])->name('CargarBC');
        Route::post('CargarClientes', [ReporteVentasController::class, 'CargarClientes'])->name('CargarClientes'); // solo POST: modifica datos en BC
    });

//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////Gestion Documental/////////////////////////////////////////////////////

//Route::get('/Share', [FileController::class, 'getFolderInmuebleCliente'])->name('files.index');

Route::get('/DocumentalComercial/{idInmueble}/{email?}', [FileController::class, 'SiimComercial'])
    ->where('email', '.*')
    ->name('Siim.Comercial');

Route::get('/filtrar-inmuebles', [ComercialController::class, 'filtrarInmuebles']);
Route::get('/Comercial', [ComercialController::class, 'Inicio'])->name('Comercial.index');
Route::get('/Comercial/{id}/edit', [ComercialController::class, 'Editar'])->name('Comercial.edit');
Route::get('/Comercial/{sharepoint_id}/{Id}/edit', [ComercialController::class, 'EditarIndicidual'])->name('Comercial.EditarIndicidual');
Route::put('/Comercial/Actualizar', [ComercialController::class, 'Actualizar'])->name('Comercial.update');
Route::put('/Comercial/ActualizarIndiv', [ComercialController::class, 'ActualizarIndiv'])->name('Comercial.ActualizarIndiv');
Route::get('/Comercial/{id}/cargar', [ComercialController::class, 'VistaSubirDocumentos'])->name('Comercial.VistaSubir');
Route::get('/Comercial/{id}/Eliminar', [ComercialController::class, 'VistaEliminarDocumento'])->name('Comercial.VistaDelete');
Route::delete('/documentos/eliminar', [ComercialController::class, 'EliminarDocumento'])->name('Comercial.eliminar');
Route::post('/Comercial/subir', [ComercialController::class, 'CargarDocumentos'])->name('Comercial.subir');

Route::get('/DocumentalExpInmueble/{idInmueble}/{email?}', [FileController::class, 'SiimExpInmueble'])
    ->where('email', '.*')
    ->name('Siim.ExpInmueble');

Route::get('/filtrar-ExpInmueble', [ExpInmuebleController::class, 'filtrarExpInmueble']);
Route::get('/ExpInmueble', [ExpInmuebleController::class, 'Inicio'])->name('ExpInmueble.index');
Route::get('/ExpInmueble/{id}/edit', [ExpInmuebleController::class, 'Editar'])->name('ExpInmueble.edit');
Route::put('/ExpInmueble/Actualizar', [ExpInmuebleController::class, 'Actualizar'])->name('ExpInmueble.update');
Route::get('/ExpInmueble/{id}/cargar', [ExpInmuebleController::class, 'VistaSubirDocumentos'])->name('ExpInmueble.VistaSubir');
Route::get('/ExpInmueble/{id}/Eliminar', [ExpInmuebleController::class, 'VistaEliminarDocumento'])->name('ExpInmueble.VistaDelete');
Route::delete('/ExpInmueble/eliminar', [ExpInmuebleController::class, 'EliminarDocumento'])->name('ExpInmueble.eliminar');
Route::post('/ExpInmueble/subir', [ExpInmuebleController::class, 'CargarDocumentos'])->name('ExpInmueble.subir');

?>
