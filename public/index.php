<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

$raiz = dirname(__DIR__);
$titulo = 'Inicio';

require $raiz . '/views/layout/encabezado.php';
?>
<section class="panel-inicio" aria-labelledby="presentacion-titulo">
    <p class="panel-inicio-etiqueta">Caso F Â· Fase 2</p>
    <h2 id="presentacion-titulo">Sistema de facturacion de servicios</h2>
    <p>Aplicacion web para gestionar servicios facturables, consultar facturas y revisar reportes de facturacion.</p>
</section>
<section class="panel-modulos" aria-labelledby="modulos-titulo">
    <h2 id="modulos-titulo">Accesos principales</h2>
    <div class="grid-tarjetas">
        <article class="tarjeta modulo-tarjeta">
            <h3>Servicios</h3>
            <p>Consulta y administracion de los servicios disponibles.</p>
            <a class="boton" href="/servicios/index.php">Ir a Servicios</a>
        </article>
        <article class="tarjeta modulo-tarjeta">
            <h3>Facturas</h3>
            <p>Consulta las facturas registradas y sus detalles.</p>
            <a class="boton" href="/facturas/index.php">Ir a Facturas</a>
        </article>
        <article class="tarjeta modulo-tarjeta">
            <h3>Reporte</h3>
            <p>Revisa la informacion consolidada de facturacion.</p>
            <a class="boton" href="/reporte/index.php">Ir al Reporte</a>
        </article>
    </div>
</section>
<section class="panel-resumen" aria-labelledby="resumen-titulo">
    <h2 id="resumen-titulo">Resumen del sistema</h2>
    <p>El Caso F integra diferentes tipos de servicio bajo un modelo de facturacion orientado a objetos, con persistencia de datos y acceso web a sus operaciones.</p>
    <p>Para consultar los registros actuales, utiliza los accesos a cada modulo. Este panel no presenta cifras no verificadas.</p>
</section>
<?php require $raiz . '/views/layout/pie.php'; ?>
