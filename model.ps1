# PowerShell Script to Generate Laravel Eloquent Models for VenuePro

# --- Configuration ---
$ModelsPath = "app/Models"
$TraitsPath = "app/Traits"

# --- Ensure Directories Exist ---
if (-not (Test-Path $ModelsPath)) {
    New-Item -ItemType Directory -Force -Path $ModelsPath
    Write-Host "Created directory: $ModelsPath"
}
if (-not (Test-Path $TraitsPath)) {
    New-Item -ItemType Directory -Force -Path $TraitsPath
    Write-Host "Created directory: $TraitsPath"
}

# --- Trait Definition: BelongsToTenant ---
$BelongsToTenantContent = @"
<?php

namespace App\Traits;

use App\Models\Company;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model \$model) {
            if (session()->has('company_id')) {
                \$model->company_id = session('company_id');
            }
        });
    }

    public function company()
    {
        return \$this->belongsTo(Company::class);
    }
}
"@
$BelongsToTenantContent | Out-File -FilePath "$TraitsPath/BelongsToTenant.php" -Encoding utf8

Write-Host "✅ Created Trait: BelongsToTenant.php"

# --- Model Definitions ---

# 1. Company Model
$CompanyContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Tenant;

class Company extends Tenant
{
    use HasFactory;

    protected \$fillable = [
        'name',
        'domain',
        'database',
    ];

    public function users()
    {
        return \$this->hasMany(User::class);
    }
}
"@
$CompanyContent | Out-File -FilePath "$ModelsPath/Company.php" -Encoding utf8
Write-Host "✅ Created Model: Company.php"

# 2. User Model
$UserContent = @"
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\BelongsToTenant;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, BelongsToTenant;

    protected \$fillable = [
        'name',
        'email',
        'password',
        'company_id',
    ];

    protected \$hidden = [
        'password',
        'remember_token',
    ];

    protected \$casts = [
        'email_verified_at' => 'datetime',
    ];
}
"@
$UserContent | Out-File -FilePath "$ModelsPath/User.php" -Encoding utf8
Write-Host "✅ Created Model: User.php"

# 3. Building Model
$BuildingContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Building extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'name',
        'address',
        'company_id',
    ];

    public function rooms()
    {
        return \$this->hasMany(Room::class);
    }
}
"@
$BuildingContent | Out-File -FilePath "$ModelsPath/Building.php" -Encoding utf8
Write-Host "✅ Created Model: Building.php"

# 4. Room Model
$RoomContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Room extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'name',
        'building_id',
        'capacity',
        'type',
        'company_id',
    ];

    public function building()
    {
        return \$this->belongsTo(Building::class);
    }

    public function amenities()
    {
        return \$this->belongsToMany(Amenity::class, 'room_amenity');
    }

    public function bookings()
    {
        return \$this->hasMany(Booking::class);
    }
}
"@
$RoomContent | Out-File -FilePath "$ModelsPath/Room.php" -Encoding utf8
Write-Host "✅ Created Model: Room.php"

# 5. Booking Model
$BookingContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Booking extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'room_id',
        'user_id',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'company_id',
    ];

    protected \$casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function room()
    {
        return \$this->belongsTo(Room::class);
    }

    public function user()
    {
        return \$this->belongsTo(User::class);
    }

    public function attendees()
    {
        return \$this->belongsToMany(User::class, 'booking_attendee');
    }
}
"@
$BookingContent | Out-File -FilePath "$ModelsPath/Booking.php" -Encoding utf8
Write-Host "✅ Created Model: Booking.php"

# 6. Amenity Model
$AmenityContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Amenity extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'name',
        'company_id',
    ];

    public function rooms()
    {
        return \$this->belongsToMany(Room::class, 'room_amenity');
    }
}
"@
$AmenityContent | Out-File -FilePath "$ModelsPath/Amenity.php" -Encoding utf8
Write-Host "✅ Created Model: Amenity.php"

# 7. RoomAvailability Model
$RoomAvailabilityContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class RoomAvailability extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$table = 'room_availability';

    protected \$fillable = [
        'room_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'company_id',
    ];

    protected \$casts = [
        'is_available' => 'boolean',
    ];

    public function room()
    {
        return \$this->belongsTo(Room::class);
    }
}
"@
$RoomAvailabilityContent | Out-File -FilePath "$ModelsPath/RoomAvailability.php" -Encoding utf8
Write-Host "✅ Created Model: RoomAvailability.php"

# 8. DailyBreakTime Model
$DailyBreakTimeContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class DailyBreakTime extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'room_availability_id',
        'start_time',
        'end_time',
        'company_id',
    ];

    public function roomAvailability()
    {
        return \$this->belongsTo(RoomAvailability::class);
    }
}
"@
$DailyBreakTimeContent | Out-File -FilePath "$ModelsPath/DailyBreakTime.php" -Encoding utf8
Write-Host "✅ Created Model: DailyBreakTime.php"

# 9. BlockedDate Model
$BlockedDateContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class BlockedDate extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'room_id',
        'date',
        'reason',
        'company_id',
    ];

    protected \$casts = [
        'date' => 'date',
    ];

    public function room()
    {
        return \$this->belongsTo(Room::class);
    }
}
"@
$BlockedDateContent | Out-File -FilePath "$ModelsPath/BlockedDate.php" -Encoding utf8
Write-Host "✅ Created Model: BlockedDate.php"

# 10. ExternalBookingRequest Model
$ExternalBookingRequestContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ExternalBookingRequest extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'room_id',
        'requester_name',
        'requester_email',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'company_id',
    ];

    protected \$casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function room()
    {
        return \$this->belongsTo(Room::class);
    }
}
"@
$ExternalBookingRequestContent | Out-File -FilePath "$ModelsPath/ExternalBookingRequest.php" -Encoding utf8
Write-Host "✅ Created Model: ExternalBookingRequest.php"

# 11. SubscriptionPlan Model
$SubscriptionPlanContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected \$fillable = [
        'name',
        'price',
        'billing_cycle',
        'features',
    ];

    protected \$casts = [
        'features' => 'array',
    ];
}
"@
$SubscriptionPlanContent | Out-File -FilePath "$ModelsPath/SubscriptionPlan.php" -Encoding utf8
Write-Host "✅ Created Model: SubscriptionPlan.php"

# 12. Module Model
$ModuleContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected \$fillable = [
        'name',
        'description',
    ];
}
"@
$ModuleContent | Out-File -FilePath "$ModelsPath/Module.php" -Encoding utf8
Write-Host "✅ Created Model: Module.php"

# 13. Invoice Model
$InvoiceContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Invoice extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'company_id',
        'issue_date',
        'due_date',
        'total_amount',
        'status',
    ];

    protected \$casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function company()
    {
        return \$this->belongsTo(Company::class);
    }

    public function items()
    {
        return \$this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return \$this->hasMany(Payment::class);
    }
}
"@
$InvoiceContent | Out-File -FilePath "$ModelsPath/Invoice.php" -Encoding utf8
Write-Host "✅ Created Model: Invoice.php"

# 14. InvoiceItem Model
$InvoiceItemContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected \$fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total',
    ];

    public function invoice()
    {
        return \$this->belongsTo(Invoice::class);
    }
}
"@
$InvoiceItemContent | Out-File -FilePath "$ModelsPath/InvoiceItem.php" -Encoding utf8
Write-Host "✅ Created Model: InvoiceItem.php"

# 15. Payment Model
$PaymentContent = @"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Payment extends Model
{
    use HasFactory, BelongsToTenant;

    protected \$fillable = [
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_id',
        'company_id',
    ];

    protected \$casts = [
        'payment_date' => 'date',
    ];

    public function invoice()
    {
        return \$this->belongsTo(Invoice::class);
    }
}
"@
$PaymentContent | Out-File -FilePath "$ModelsPath/Payment.php" -Encoding utf8
Write-Host "✅ Created Model: Payment.php"


Write-Host "`n🚀 All models and traits have been generated successfully!"
