<?php

namespace App\Http\Controllers\Api\V1;

use App\Brand;
use App\Business;
use App\BusinessReview;
use App\Categories;
use App\City;
use App\Enquiry;
use App\Product;
use App\Helper\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\BusinessResource;
use App\Http\Resources\Admin\ProductCollection;
use App\Http\Resources\Admin\UserResource;
use App\Http\Resources\BrandCollection;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CityCollection;
use App\Http\Resources\ProductResource;
use App\MailTemplate;
use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Validator;

class HomeController extends Controller
{
    //

    public function CategoriesList(Request $request)
    {
        # code...
        try {
            //code...
            $parameters = $request->all();
            extract($parameters);
            $parrent_id = ($request->parrent_id)??0;
            $categories = Categories::where('parent', $parrent_id)->get();
            if(!empty($categories) && count($categories) > 0){
                $this->response = new CategoryCollection($categories);
                return ResponseBuilder::success($this->response, "Category List");
            }else{
                return ResponseBuilder::error("No Data found", $this->badRequest);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        }
        
    }

    public function BrandsList(Request $request)
    {
        # code...
        try {
            //code...
            $brands = Brand::all();
            if(!empty($brands) && count($brands) > 0){
                $this->response = new BrandCollection($brands);
                return ResponseBuilder::success($this->response, "Brand List");
            }else{
                return ResponseBuilder::error("No Data found", $this->badRequest);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        }
    }

    public function CountryList()
    {
        # code...
        try {
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            }
            //code...
            $countries = $this->getCountry();
            if(!empty($countries) && count($countries) > 0){
                $countryList = [];
                foreach ($countries as $key => $value)
                    $countryList[] = [
                        'id' => (int)$key, 
                        'name' => (string)$value,
                        'is_selected' => (isset($user->country) && $user->country == (int)$key)?true:false
                    ];
                return ResponseBuilder::success($countryList, "Country List");
            }else{
                return ResponseBuilder::error("No Data found", $this->badRequest);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        } 
    }

    public function StateList(Request $request){
        # code...
        try {
            //code...
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            }
            $states = $this->getStates($request->country_id);
            if(!empty($states) && count($states) > 0){
                $stateList = [];
                foreach ($states as $key => $value)
                    $stateList[] = [
                        'id' => (int)$key, 
                        'name' => (string)$value,
                        'is_selected' => (isset($user->state) && $user->state == (int)$key)?true:false
                    ];
                return ResponseBuilder::success($stateList, "States List");
            }else{
                return ResponseBuilder::error("No Data found", $this->badRequest);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        } 
    }

    public function CityList(Request $request)
    {
        # code...
        try {
            //code...
            $cities = City::where('state_id', $request->state_id)->get();
            if(!empty($cities) && count($cities) > 0){
                $this->response = new CityCollection($cities);
                return ResponseBuilder::success($this->response, "City List");
            }else{
                return ResponseBuilder::error("No Data found", $this->badRequest);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        } 
    }

    // All Products
    public function AllProducts(Request $request)
    {
        # code...
        try {        
            // if (Auth::guard('api')->check()) {
            //     $user = Auth::guard('api')->user();
            //     $user_id = $user->id;
            // } else {
            //     return ResponseBuilder::error(__("Unauthorized Access"), $this->unauthorized);
            // }
            $parameters = $request->all();
            extract($parameters);

            $pagination = !empty($per_page) ? $per_page : 10;
            
            $query = Product::with('user')->where('status', 1);

            if(isset($category_id)) {
                $query->where('category_id', $category_id);
            }
            if(isset($search)) {
                $query->where('name', 'like', '%'.$search.'%');
            }
            
            if(isset($child_category_id)) {
                $query->orWhere('child_category_id', $child_category_id);
            }
            if(isset($sub_child_category_id)) {
                $query->orWhere('sub_child_category_id', $sub_child_category_id);
            }
            if(isset($brand_id)) {
                $query->where('brand_id', $brand_id);
            }

            if(isset($min_price)) {
                $query->where('price', '>', $min_price);
            }

            if(isset($max_price)) {
                $query->where('price', '<', $max_price);
            }

            $products = $query->paginate($pagination);
            if(count($products) > 0){
                $this->response = new ProductCollection($products);
                return ResponseBuilder::successWithPagination($products, $this->response, "Products Lists", $this->success);
            } else {
                return ResponseBuilder::error(__("No Result Found!"), $this->badRequest);
            }

        } catch (Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        }
    }

    // Single Product
    public function singleProduct($id)
    {
        try {
            $product = Product::with('business','user')->where('id', $id)->first();
            if(empty($product)) {
                return ResponseBuilder::error("Product Not Found!", $this->notFound);
            }
            $this->response->product = new ProductResource($product);
            $this->response->user = new UserResource($product->user);
            $this->response->business = new BusinessResource($product->business);
            return ResponseBuilder::success($this->response, "Single Product Details.");

        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        }
        
    }

    public function legalStatus()
    {
        # code...
        try {
            //code...
            $legalStatus = ['Sole Proprietorship', 'Partnership', 'Limited Liability Company (LLC)', 'C Corp', 'S Corp', 'Close Corporation'];
            return ResponseBuilder::success($legalStatus, "Legal Status");
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        } 
    }

    public function getPage(Request $request)
    {
        # code...
        $parameters = $request->all();
        extract($parameters);
        try {
            //code...
            $page = Page::where('slug', $slug)->first();
            if(!$page) {
                return ResponseBuilder::error(__("Page Not Found!"), $this->notFound);
            } else {
                return ResponseBuilder::success($page, "Page Details");
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        }
    }

    public function submitContact(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'subject' => 'required',
        ], [
            'name.required' => 'Your Name is required!',
            'email.required' => 'Your email Id is required!',
            'phone.required' => 'Your phone number is required!',
            'subject.required' => 'Subject is required!',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        }
        $parameters = $request->all();
        extract($parameters);

        try {
            //code...
            $enquiry = Enquiry::create([
                'name' => ($name)??'',
                'email' => ($email)??'',
                'phone' => ($phone)??'',
                'subject' => ($subject)??'',
                'message' => ($message)??'',
            ]);
            if($enquiry) {

                $mailData = MailTemplate::where('category', 'contact-us')->first();
                $basicInfo = [
                    '{name}' => ($name)??'',
                    '{email}' => ($email)??'',
                    '{phone}' => ($phone)??'',
                    '{subject}' => ($subject)??'',
                    '{message}' => ($message)??'',
                    '{siteName}' => '',
                ];
                $this->SendMail($email, $mailData, $basicInfo);

                return ResponseBuilder::successMessage('Your Message has received. Thank you to contact us!', $this->success);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        }
        
        
    }
    
    // Add Review
    function addReview(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'review' => 'required',
            'stars' => 'required',
        ], [
            'name.required' => 'Your Name is required!',
            'email.required' => 'Your email Id is required!',
            'review.required' => 'Your review is required!',
            'subject.required' => 'Subject is required!',
            'stars.required' => 'Minimum 1 star required!'
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        }
        $parameters = $request->all();
        extract($parameters);

        try {
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error(__("Unauthorized Access"), $this->unauthorized);
            }
            $data = [
                'business_id' => $business_id,
                'name' => $name,
                'email' => $email,
                'review' => $review,
                'stars' => $stars,
            ];
            BusinessReview::create($data);
            return ResponseBuilder::successMessage("Review Added Successfully.", $this->success);

        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        }
    }

    // singleBusinessDetails
    public function singleBusinessDetails($business_id)
    {
        # code...
        try {
            //code...
            $business  = Business::select('businesses.*', 'categories.name as category_name')
                    ->with('user')
                    ->where('businesses.id', $business_id)
                    ->join('categories', 'businesses.category', '=', 'categories.id')
                    ->first();

            if(!$business) {
                return ResponseBuilder::error(__("Business Not Found!"), $this->notFound);
            }
            $business->products = [];
            if(isset($business->user->id)) {
                $business->products = Product::where('user_id', $business->user->id)->get();
            }

            $this->response = new BusinessResource($business);
            return ResponseBuilder::success($this->response, "Business Details", $this->success);
            
        } catch (\Throwable $th) {
            //throw $th;
            return ResponseBuilder::error(__($th->getMessage()), $this->serverError);
        }
        
    }
}
