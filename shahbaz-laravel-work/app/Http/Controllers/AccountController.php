<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\GuestProfile;
use App\Services\Cart\GuestIdentity;
use App\Services\Cart\CartService;
use App\Services\Wishlist\WishlistService;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private readonly GuestIdentity $guest, private readonly CartService $carts, private readonly WishlistService $wishlists) {}
    public function index(): View
    {
        $hash = $this->guest->hash();
        return view('account.index', [
            'guestProfile' => $hash ? GuestProfile::where('guest_token_hash', $hash)->where('expires_at','>',now())->first() : null,
            'savedProductIds' => $this->wishlists->current()?->items->pluck('id')->map(fn ($id)=>(int)$id)->all() ?? [],
        ]);
    }
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());
        return back()->with('success', 'اطلاعات حساب کاربری به‌روزرسانی شد.');
    }
    public function saveGuestProfile(Request $request): RedirectResponse
    {
        abort_if($request->user(), 403);
        $data = $request->validate(['name'=>['required','string','min:2','max:120'],'mobile'=>['nullable','string','max:32'],'email'=>['nullable','email','max:254']]);
        $mobile = $data['mobile'] ? PhoneNormalizer::normalize($data['mobile']) : null;
        $hash = $this->guest->hash(true);
        GuestProfile::updateOrCreate(['guest_token_hash'=>$hash], [
            'name'=>$data['name'], 'mobile'=>$mobile, 'email'=>$data['email'] ?? null, 'expires_at'=>now()->addDays(30),
        ]);
        return back()->with('success', 'اطلاعات مهمان به‌صورت امن ذخیره شد.');
    }
}
