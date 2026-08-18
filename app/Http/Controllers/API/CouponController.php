<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CouponServices;
use App\Helpers\ResponseCode;
use App\Helpers\Helper;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private $couponServices;

    public function __construct(CouponServices $couponServices)
    {
        $this->couponServices = $couponServices;
    }

    /**
     * Apply coupon API (Read-Only validation).
     */
    public function apply(Request $request)
    {
        try {
            $request->validate([
                'coupon_code' => 'required|string',
            ]);

            $customerId = auth('customer')->user()->id;
            $couponCode = $request->coupon_code;

            $result = $this->couponServices->validateCoupon($couponCode, $customerId);

            return $this->successResponse($result);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->customErrorResponse(
                ResponseCode::$VALIDATE,
                $e->validator->errors()->first(),
                400
            );
        } catch (\Exception $e) {
            Helper::createLogError(__FILE__ . ':' . __LINE__ . ' ' . $e);
            return $this->customErrorResponse(
                ResponseCode::$BAD_REQUEST,
                $e->getMessage(),
                400
            );
        }
    }

    /**
     * Get list of active coupons for a specific course.
     */
    public function getCouponsByCourse($courseId)
    {
        try {
            $coupons = $this->couponServices->getCouponsForCourse($courseId);
            return $this->successResponse($coupons);
        } catch (\Exception $e) {
            Helper::createLogError(__FILE__ . ':' . __LINE__ . ' ' . $e);
            return $this->customErrorResponse(
                ResponseCode::$BAD_REQUEST,
                $e->getMessage(),
                400
            );
        }
    }
}
