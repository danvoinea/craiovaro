use Illuminate\Support\Facades\Route;
use App\Actions\GetSystemStatus;

Route::get('/status', function (GetSystemStatus $action) {
    return response()->json([
        'ok' => true,
        'data' => $action->execute(),
    ]);
});

