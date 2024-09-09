<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\App;
use App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/img/{path}', [Controllers\ImagenController::class, 'show'])->where('path', '.*')->name('image');

//AUTH
Route::prefix('auth')->group(function (){
    Route::name('auth.')->group(function(){

        Route::prefix('torneo')->group(function () {
            Route::name('torneo.')->group(function () {
                Route::get('/view', [Auth\TorneoController::class, 'index'])->name('index');
                Route::get('grupo/partialView/{torneo_id}/{categoria_id}/{tipo?}', [Auth\TorneoController::class, 'grupoPartialView'])->name('grupo.partialView');
                Route::get('grupo/{id}/{torneo_categoria_id?}/{fase?}/{landing?}', [Auth\TorneoController::class, 'grupo'])->name('grupo');
                Route::get('grupo/tabla/partialView/{id}/{torneo_categoria_id}/{torneo_grupo_id}/{landing?}', [Auth\TorneoController::class, 'grupoTablaPartialView'])->name('grupo.partido.partialView');

                Route::get('fase-final/mapa/partialView/{torneo}/{torneo_categoria_id}/{landing?}', [Auth\TorneoController::class, 'faseFinalMapaPartialView'])->name('faseFinal.mapa.partialView');

                Route::get('fase-final-final/{torneo}/{torneo_categoria_id}/{landing?}', [Auth\TorneoController::class, 'faseFinal'])->name('faseFinal');

                Route::get('ranking/{torneo}/{torneo_categoria_id}/{landing?}', [Auth\TorneoController::class, 'ranking'])->name('ranking');
            });
        });
    });
});

Route::group(['middleware' => 'auth:web'], function() {

    Route::prefix('auth')->group(function (){
        Route::name('auth.')->group(function(){

            Route::group(['roles' => ['Comunidad']], function () {
                Route::middleware('auth.route.access')->group(function () {

                    Route::prefix('rankings')->group(function (){
                        Route::name('rankings.')->group(function(){
                            Route::get('/', [Auth\RakingController::class, 'index'])->name('index');
                            Route::get('partialView', [Auth\RakingController::class, 'partialView'])->name('partialView');
                        });
                    });

                    Route::prefix('torneo')->group(function (){
                        Route::name('torneo.')->group(function(){
                            Route::get('/', [Auth\TorneoController::class, 'index'])->name('index');
                            Route::post('store', [Auth\TorneoController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\TorneoController::class, 'partialView'])->name('partialView');
                            Route::get('partialViewBackground/{id}/{torneo_category}', [Auth\TorneoController::class, 'partialViewBackground'])->name('partialViewBackground');
                            Route::post('updateBackground', [Auth\TorneoController::class, 'updateBackground'])->name('updateBackground');
                            Route::post('delete', [Auth\TorneoController::class, 'delete'])->name('delete');
                            Route::post('finish', [Auth\TorneoController::class, 'finish'])->name('finish');

                            Route::get('categorias', [Auth\TorneoController::class, 'categorias'])->name('categorias');
                            Route::post('categoria/store', [Auth\TorneoController::class, 'categoriaStore'])->name('categoria.store');

                            Route::get('categoria/cambiarOrdenPartialView/{torneo_id}', [Auth\TorneoController::class, 'cambiarOrdenPartialView'])->name('categoria.cambiarOrdenPartialView');
                            Route::post('categoria/cambiarOrdenStore', [Auth\TorneoController::class, 'cambiarOrdenStore'])->name('categoria.cambiarOrdenStore');

                            Route::post('grupo/cambiarNombre', [Auth\TorneoController::class, 'grupoCambiarNombre'])->name('grupo.cambiarNombre');
                            Route::post('grupo/validacionGrupo', [Auth\TorneoController::class, 'grupoValidacionGrupo'])->name('grupo.validacionGrupo');

                            //Route::get('grupo/partialView/{torneo_id}/{categoria_id}/{tipo?}', [Auth\TorneoController::class, 'grupoPartialView'])->name('grupo.partialView');
                            //Route::get('grupo/{id}/{torneo_categoria_id?}/{fase?}/{landing?}', [Auth\TorneoController::class, 'grupo'])->name('grupo');

                            Route::get('grupo/manual/partialView/{torneo_id}/{categoria_id}/{tipo?}', [Auth\TorneoController::class, 'grupoManualPartialView'])->name('grupo.manualPartialView');
                            Route::post('grupo/manual/store', [Auth\TorneoController::class, 'grupoManualStore'])->name('grupo.manualStore');

                            Route::get('grupos/agregar/partialView/{torneo_id}/{categoria_id}', [Auth\TorneoController::class, 'grupoAgregarPartialView'])->name('grupo.agregarPartialView');

                            Route::get('grupos/export/json', [Auth\TorneoController::class, 'exportGrupoJson'])->name('exportGrupoJson');

                            Route::get('export/mapa/json', [Auth\TorneoController::class, 'exportMapaJson'])->name('exportMapaJson');

                            /*Route::get('grupo/partialView/{id}/{torneo_categoria_id?}', [Auth\TorneoController::class, 'grupoPartialView'])->name('grupo.partialView');
                            Route::post('grupo/store', [Auth\TorneoController::class, 'grupoStore'])->name('grupo.store');
                            Route::post('grupo/delete', [Auth\TorneoController::class, 'grupoDelete'])->name('grupo.delete');*/
                            Route::get('jugador/export/json', [Auth\TorneoController::class, 'exportJugadorJson'])->name('exportJugadorJson');

                            Route::get('jugador/list-json', [Auth\TorneoController::class, 'jugadorListJson'])->name('jugadorListJson');
                            Route::get('jugador/partialView/{torneo_categoria}', [Auth\TorneoController::class, 'jugadorPartialView'])->name('jugador.partialView');
                            Route::get('jugador/available/list-json', [Auth\TorneoController::class, 'jugadorAvailableListJson'])->name('jugadorAvailableListJson');
                            Route::get('jugador/available/classification/list-json', [Auth\TorneoController::class, 'jugadorAvailableClassificationListJson'])->name('jugadorAvailableClassificationListJson');
                            Route::get('jugador/available/not-classification/list-json', [Auth\TorneoController::class, 'jugadorAvailableNotClassificationListJson'])->name('jugadorAvailableNotClassificationListJson');

                            Route::get('jugador/partialViewChange/{torneo}/{torneo_categoria}/{jugador}', [Auth\TorneoController::class, 'jugadorPartialViewChange'])->name('jugador.partialView');

                            Route::post('jugador/store', [Auth\TorneoController::class, 'jugadorStore'])->name('jugador.store');
                            Route::post('jugador/change', [Auth\TorneoController::class, 'jugadorChange'])->name('jugador.change');
                            Route::post('jugador/classification/change', [Auth\TorneoController::class, 'jugadorClassificationChange'])->name('jugador.classification.change');

                            Route::post('jugador/delete', [Auth\TorneoController::class, 'jugadorDelete'])->name('jugador.delete');
                            Route::post('jugador/delete/masivo', [Auth\TorneoController::class, 'jugadorDeleteMasivo'])->name('jugador.deleteMasivo');

                            Route::get('jugador/partialViewMultipleZona/{torneo}/{torneo_categoria}', [Auth\TorneoController::class, 'jugadorPartialViewMultipleZona'])->name('jugador.partialViewMultipleZona');
                            Route::get('jugador/partialViewZona/{id}', [Auth\TorneoController::class, 'jugadorPartialViewZona'])->name('jugador.partialViewZona');
                            Route::post('jugador/zona/store', [Auth\TorneoController::class, 'jugadorZonaStore'])->name('jugador.zona.store');
                            Route::get('jugador/reporte/{tipo}/{torneo}/{torneo_categoria}', [Auth\TorneoController::class, 'jugadorReporte'])->name('jugador.reporte');

                            //Route::get('grupo/tabla/partialView/{id}/{torneo_categoria_id}/{torneo_grupo_id}', [Auth\TorneoController::class, 'grupoTablaPartialView'])->name('grupo.partido.partialView');
                            Route::post('grupo/store', [Auth\TorneoController::class, 'grupoStore'])->name('grupo.store');
                            Route::post('grupo/delete', [Auth\TorneoController::class, 'grupoDelete'])->name('grupo.delete');

                            Route::get('grupo/partido/partialView/{id}/{torneo_categoria_id}/{torneo_grupo_id}', [Auth\TorneoController::class, 'grupoPartidoPartialView'])->name('grupo.partido.partialView');
                            Route::post('grupo/partido/store', [Auth\TorneoController::class, 'grupoPartidoStore'])->name('grupo.partido.store');

                            Route::post('partido/store', [Auth\TorneoController::class, 'partidoStore'])->name('partido.store');
                            Route::post('partido/reset', [Auth\TorneoController::class, 'partidoReset'])->name('partido.reset');
                            Route::post('partido/storeMultiple', [Auth\TorneoController::class, 'partidoStoreMultiple'])->name('partido.store.multiple');

                            Route::get('partido/export/json', [Auth\TorneoController::class, 'partidoGenerateJson'])->name('partido.generate.json');

                            Route::post('fase-final-first/store', [Auth\TorneoController::class, 'faseFinalFirstStore'])->name('faseFinalFirstStore.store');

                            Route::get('fase-final/players/terceros/{torneo}/{torneo_categoria_id}', [Auth\TorneoController::class, 'faseFinalPlayerTerceros'])->name('faseFinalPlayerTerceros');
                            Route::post('fase-final/players/terceros', [Auth\TorneoController::class, 'faseFinalPlayerTercerosStore'])->name('faseFinalPlayerTercerosStore');

                            Route::get('fase-final/players/changes/{torneo}/{torneo_categoria_id}', [Auth\TorneoController::class, 'faseFinalPlayersChanges'])->name('faseFinalPlayersChanges');

                            //Route::get('fase-final/{torneo}/{torneo_categoria_id}/{landing?}', [Auth\TorneoController::class, 'faseFinal'])->name('faseFinal');

                            Route::post('fase-final/store', [Auth\TorneoController::class, 'faseFinalStore'])->name('faseFinal.store');
                            Route::post('fase-final/reload', [Auth\TorneoController::class, 'faseFinalReload'])->name('faseFinal.reload');
                            Route::post('fase-final/delete', [Auth\TorneoController::class, 'faseFinalDelete'])->name('faseFinal.delete');

                            //Route::get('fase-final/mapa/partialView/{torneo}/{torneo_categoria_id}/{landing?}', [Auth\TorneoController::class, 'faseFinalMapaPartialView'])->name('faseFinal.mapa.partialView');

                            Route::get('fase-final/prepartido/jugador/list-json', [Auth\TorneoController::class, 'faseFinalPrePartidoJugadorListJson'])->name('faseFinal.prepartido.jugador.listJson');
                            Route::post('fase-final/prepartido/store', [Auth\TorneoController::class, 'faseFinalPrePartidoStore'])->name('faseFinal.prepartido.store');
                            Route::post('fase-final/prepartido/finish', [Auth\TorneoController::class, 'faseFinalPrePartidoFinish'])->name('faseFinal.prepartido.finish');
                            Route::post('fase-final/prepartido/delete', [Auth\TorneoController::class, 'faseFinalPrePartidoDelete'])->name('faseFinal.prepartido.delete');

                            Route::get('fase-final/prepartido/partialView/{torneo}/{torneo_categoria_id}/{id}/{position}/{bracket}', [Auth\TorneoController::class, 'faseFinalPrePartidoPartialView'])->name('faseFinal.prepartido.partialView');
                            Route::post('fase-final/prepartido/jugadorInfo', [Auth\TorneoController::class, 'faseFinalPrePartidoJugadorInfo'])->name('faseFinal.prepartido.jugadorInfo');

                            Route::post('fase-final/partido/validate/partialView', [Auth\TorneoController::class, 'faseFinalPartidoValidatePartialView'])->name('faseFinal.partido.validatePartialView');
                            Route::get('fase-final/partido/partialView/{id}/{position}', [Auth\TorneoController::class, 'faseFinalPartidoPartialView'])->name('faseFinal.partido.partialView');
                            Route::post('fase-final/partido/store', [Auth\TorneoController::class, 'faseFinalPartidoStore'])->name('faseFinal.partido.store');

                            //Route::get('ranking/{torneo}/{torneo_categoria_id}/{landing?}', [Auth\TorneoController::class, 'ranking'])->name('ranking');
                        });
                    });

                    Route::prefix('jugador')->group(function (){
                        Route::name('jugador.')->group(function(){
                            Route::get('/', [Auth\JugadorController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\JugadorController::class, 'listJson'])->name('listJson');
                            Route::post('store', [Auth\JugadorController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\JugadorController::class, 'partialView'])->name('partialView');
                            Route::post('account', [Auth\JugadorController::class, 'account'])->name('account');
                            Route::post('account/delete', [Auth\JugadorController::class, 'accountDelete'])->name('accountDelete');
                            Route::post('delete', [Auth\JugadorController::class, 'delete'])->name('delete');
                            Route::post('delete/masivo', [Auth\JugadorController::class, 'deleteMasivo'])->name('deleteMasivo');
                            Route::get('partialViewimportExcel', [Auth\JugadorController::class, 'partialViewImportExcel'])->name('partialViewImportExcel');
                            Route::post('importarExcel', [Auth\JugadorController::class, 'importarExcel'])->name('importarExcel');
                        });
                    });

                    Route::prefix('formato')->group(function (){
                        Route::name('formato.')->group(function(){
                            Route::get('/', [Auth\FormatoController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\FormatoController::class, 'listJson'])->name('listJson');
                            Route::post('store', [Auth\FormatoController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\FormatoController::class, 'partialView'])->name('partialView');
                            Route::post('delete', [Auth\FormatoController::class, 'delete'])->name('delete');
                        });
                    });

                    Route::prefix('categoria')->group(function (){
                        Route::name('categoria.')->group(function(){
                            Route::get('/', [Auth\CategoriaController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\CategoriaController::class, 'listJson'])->name('listJson');
                            Route::post('store', [Auth\CategoriaController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\CategoriaController::class, 'partialView'])->name('partialView');
                            Route::post('delete', [Auth\CategoriaController::class, 'delete'])->name('delete');
                        });
                    });

                    Route::prefix('zona')->group(function (){
                        Route::name('zona.')->group(function(){
                            Route::get('/', [Auth\ZonaController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\ZonaController::class, 'listJson'])->name('listJson');
                            Route::post('store', [Auth\ZonaController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\ZonaController::class, 'partialView'])->name('partialView');
                            Route::post('delete', [Auth\ZonaController::class, 'delete'])->name('delete');
                        });
                    });

                    Route::prefix('portada')->group(function (){
                        Route::name('portada.')->group(function(){
                            Route::get('/', [Auth\PortadaController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\PortadaController::class, 'listJson'])->name('listJson');
                            Route::post('store', [Auth\PortadaController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\PortadaController::class, 'partialView'])->name('partialView');
                            Route::post('delete', [Auth\PortadaController::class, 'delete'])->name('delete');
                        });
                    });

                    Route::prefix('galeria')->group(function (){
                        Route::name('galeria.')->group(function(){
                            Route::get('/', [Auth\GaleriaController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\GaleriaController::class, 'listJson'])->name('listJson');
                            Route::post('store', [Auth\GaleriaController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\GaleriaController::class, 'partialView'])->name('partialView');
                            Route::post('delete', [Auth\GaleriaController::class, 'delete'])->name('delete');
                        });
                    });

                    Route::prefix('pagina')->group(function (){
                        Route::name('pagina.')->group(function(){
                            Route::get('/', [Auth\PaginaController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\PaginaController::class, 'listJson'])->name('listJson');
                            Route::post('store', [Auth\PaginaController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\PaginaController::class, 'partialView'])->name('partialView');
                        });
                    });


                    /*Route::prefix('grupo')->group(function (){
                        Route::name('grupo.')->group(function(){
                            Route::get('/', [Auth\GrupoController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\GrupoController::class, 'listJson'])->name('listJson');
                            Route::post('store', [Auth\GrupoController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\GrupoController::class, 'partialView'])->name('partialView');
                            Route::post('delete', [Auth\GrupoController::class, 'delete'])->name('delete');
                        });
                    });*/

                    Route::prefix('perfil')->group(function (){
                        Route::name('perfil.')->group(function(){
                            Route::get('/', [Auth\PerfilController::class, 'index'])->name('index');
                            Route::post('get-slug', [Auth\ComunidadController::class, 'getSlug'])->name('getSlug');
                            Route::get('partialView', [Auth\PerfilController::class, 'partialView'])->name('partialView');
                            Route::post('store', [Auth\PerfilController::class, 'store'])->name('store');
                        });
                    });

                    Route::prefix('puntuacion')->group(function (){
                        Route::name('puntuacion.')->group(function(){
                            Route::get('/', [Auth\PuntuacionController::class, 'index'])->name('index');
                            Route::post('store', [Auth\PuntuacionController::class, 'store'])->name('store');
                        });
                    });

                    Route::prefix('reporte')->group(function (){
                        Route::name('reporte.')->group(function(){
                            Route::get('jugador', [Auth\ReporteController::class, 'jugador'])->name('jugador');
                            Route::get('jugadorPartialView', [Auth\ReporteController::class, 'jugadorPartialView'])->name('jugadorPartialView');
                            Route::get('jugadorPartidosPartialView', [Auth\ReporteController::class, 'jugadorPartidosPartialView'])->name('jugadorPartidosPartialView');

                            Route::get('torneo', [Auth\ReporteController::class, 'torneo'])->name('torneo');
                            Route::get('torneo/exportar/pdf/{torneo}/{categoria}', [Auth\ReporteController::class, 'torneoExportarPdf'])->name('torneoExportarPdf');
                            Route::get('torneo/fase-final/exportar/pdf/{torneo}/{categoria}', [Auth\ReporteController::class, 'torneoFaseFinalExportarPdf'])->name('torneoFaseFinalExportarPdf');
                            Route::get('torneoPartialView', [Auth\ReporteController::class, 'torneoPartialView'])->name('torneoPartialView');
                        });
                    });

                });
            });

            Route::group(['roles' => ['Administrador']], function () {
                Route::middleware('auth.route.access')->group(function () {
                    Route::prefix('home')->group(function (){
                        Route::name('home.')->group(function(){
                            Route::get('/', [Auth\ComunidadController::class, 'index'])->name('index');
                            Route::get('list-json', [Auth\ComunidadController::class, 'listJson'])->name('listJson');
                            Route::post('get-slug', [Auth\ComunidadController::class, 'getSlug'])->name('getSlug');
                            Route::post('get-password', [Auth\ComunidadController::class, 'getPassword'])->name('getPassword');
                            Route::post('store', [Auth\ComunidadController::class, 'store'])->name('store');
                            Route::get('partialView/{id}', [Auth\ComunidadController::class, 'partialView'])->name('partialView');
                            Route::post('delete', [Auth\ComunidadController::class, 'delete'])->name('delete');
                        });
                    });
                });
            });

            Route::prefix('usuario')->group(function (){
                Route::name('usuario.')->group(function(){
                    Route::get('/', [Auth\UsuarioController::class, 'index'])->name('index');
                    Route::get('list-json', [Auth\UsuarioController::class, 'listJson'])->name('listJson');
                    Route::post('get-password', [Auth\UsuarioController::class, 'getPassword'])->name('getPassword');
                    Route::post('store', [Auth\UsuarioController::class, 'store'])->name('store');
                    Route::get('partialView/{id}', [Auth\UsuarioController::class, 'partialView'])->name('partialView');
                    Route::post('delete', [Auth\UsuarioController::class, 'delete'])->name('delete');
                });
            });
        });
    });

    Route::post('logout', [Auth\LoginController::class, 'logout'])->name('logout');
});

Route::prefix('auth')->group(function () {
    Route::get('login', [Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [Auth\LoginController::class, 'login'])->name('login.post');
});


//APP
Route::get('login', [App\LoginController::class, 'showLoginForm'])->name('app.login');
Route::post('login', [App\LoginController::class, 'login'])->name('app.login.post');

Route::get('login/reset-password', [App\PerfilController::class, 'showResetPassword'])->name('app.showResetPassword');
Route::post('login/reset-password', [App\PerfilController::class, 'resetPassword'])->name('resetPassword');

Route::group(['middleware' => 'auth:players'], function() {
    Route::prefix('app')->group(function () {
        Route::name('app.')->group(function (){
            Route::prefix('perfil')->group(function (){
                Route::name('perfil.')->group(function(){
                    Route::get('/', [App\PerfilController::class, 'index'])->name('index');
                    Route::post('store', [App\PerfilController::class, 'store'])->name('store');
                });
            });

            Route::post('logout', [App\LoginController::class, 'logout'])->name('logout');
        });
    });
});

Route::get('/', [App\HomeController::class, 'index'])->name('index');
Route::get('torneos', [App\HomeController::class, 'torneos'])->name('torneos');
Route::get('torneos/anios', [App\HomeController::class, 'torneosAnios'])->name('torneosAnios');
Route::get('torneos/mejores5', [App\HomeController::class, 'torneoMejores5'])->name('torneoMejores5');
Route::get('torneos/todos', [App\HomeController::class, 'torneoTodos'])->name('torneoTodos');

Route::get('rankings', [App\HomeController::class, 'rankings'])->name('rankings');
Route::get('rankings/categorias', [App\HomeController::class, 'rankingsCategorias'])->name('rankingsCategorias');

Route::get('rankings/partialView', [App\HomeController::class, 'rankingsPartialView'])->name('rankingsPartialView');
Route::get('jugadores', [App\HomeController::class, 'jugadores'])->name('jugadores');
Route::get('jugadorPartialView', [App\HomeController::class, 'jugadorPartialView'])->name('jugadorPartialView');
Route::get('jugadorPartidosPartialView', [App\HomeController::class, 'jugadorPartidosPartialView'])->name('jugadorPartidosPartialView');
Route::post('contactanos', [App\HomeController::class, 'contactanos'])->name('contactanos');
