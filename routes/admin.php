<?php

use Modules\User\Admin\VisitorController;

Route::prefix('visitor')->group(function () {
    Route::get('/', [VisitorController::class, 'index'])->name('visitor.index');
    Route::get('/realtime', [VisitorController::class, 'getRealTimeData'])->name('visitor.realtime');
    Route::get('/history', [VisitorController::class, 'history'])->name('visitor.history');
    Route::get('/statistics', [VisitorController::class, 'statistics'])->name('visitor.statistics');
    Route::get('/{id}', [VisitorController::class, 'show'])->name('visitor.show');
    Route::post('/cleanup', [VisitorController::class, 'cleanup'])->name('visitor.cleanup');
});

Route::get('/{id}/date-data',    [VisitorController::class, 'showByDate'])->name('visitor.date.data');
Route::delete('/{id}/date',      [VisitorController::class, 'deleteByDate'])->name('visitor.date.delete');
Route::get('/{id}/date-download',[VisitorController::class, 'downloadByDate'])->name('visitor.date.download');
// Admin Route
/*Route::group(['prefix'=>'admin','middleware' => ['auth','dashboard']], function() {
    Route::match(['get','post'],'/',function (){
        $module = ucfirst(htmlspecialchars('Dashboard'));
        $controller = ucfirst(htmlspecialchars($module));
        $class = "\\Modules\\$module\\Admin\\";
        $action = 'index';
        if(class_exists($class.$controller.'Controller') && method_exists($class.$controller.'Controller',$action)){
            return App::call($class.$controller.'Controller@'.$action,[]);
        }
        abort(404);
    });
    Route::match(['get','post'],'/module/{module}/{controller?}/{action?}/{param1?}/{param2?}/{param3?}',function ($module,$controller = '',$action = '',$param1 = '',$param2 = '',$param3 = ''){
        $module = ucfirst(htmlspecialchars($module));
        $controller = ucfirst(htmlspecialchars($controller));
        $class = "\\Modules\\$module\\Admin\\";
        if(!class_exists($class.$controller.'Controller')){
            $param3 = $param2;
            $param2 = $param1;
            $param1 = $action;
            $action = $controller;
            $controller = $module;
        }
        $action = $action ? $action : 'index';
        if(class_exists($class.$controller.'Controller') && method_exists($class.$controller.'Controller',$action)){
            $p = array_values(array_filter([$param1,$param2,$param3]));
            return App::call($class.$controller.'Controller@'.$action,$p);
//            return App::make($class.$controller.'Controller')->callAction($action,$p);
        }
        abort(404);
    });
});*/

