<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helper\ResponseBuilder;
use App\Http\Resources\EmployeesCollection;
use App\Http\Resources\EmployeesResource;
use App\Http\Resources\ProjectCollection;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\CompanyDocumentCollection;
use App\Http\Resources\CompanyDocumentResource;
use App\Http\Resources\EmployeeDocumentCollection;
use App\Http\Resources\EmployeeDocumentResource;
use App\Http\Resources\ProjectDocumentCollection;
use App\Http\Resources\ProjectDocumentResource;
use App\Http\Resources\TimeSheetCollection;
use App\Http\Resources\TimeSheetResource;
use App\Http\Resources\NotificationCollection;
use App\Http\Resources\NotificationResource;
use Exception;
use Hash;
use Illuminate\Http\Request;
use App\SubscriptionPlan;
use App\Industry;
use App\Client;
use App\Department;
use App\User;
use App\Sop;
use App\Employees;
use App\Project;
use App\ProjectSop;
use App\ProjectDocuments;
use App\CompanyDocument;
use App\EmployeeDocument;
use App\Company;
use App\TimeSheet;
use App\Notification;
use Validator;
use Carbon\Carbon;


class CommanController extends Controller
{
    // get subscription plan api 

    public function SubscriptionPlan()
    {
       
        try {
            $subscriptionPlans = SubscriptionPlan::all()->map(function($subscriptionPlans){
                return [
                    'title'=>$subscriptionPlans->title,
                    'description'=>$subscriptionPlans->description,
                    'amount'=>$subscriptionPlans->amount,
                    'employee_size'=>$subscriptionPlans->employee_size,
                     
                ];
            
        });
           
            return ResponseBuilder::success($subscriptionPlans, 'Subscription plans retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }
    
    //  get industry api 

    public function IndustryList()
    {
        try {
            $industry = Industry::all()->map(function($industry){
                return [
                    'title'=>$industry->title,
                ];
            
        });
           
            return ResponseBuilder::success($industry, 'Industry retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }
          
    //Department list api 

    public function DepartmentList()
    {
        try {
            
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
              
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $department = Department::where('company_id', $user->id)->get()->map(function( $department){
            return  [
                    'id'=>$department->id,
                    'title'=>$department->title,
                ];
            });
        
           
            return ResponseBuilder::success($department, 'Department retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

    //client list api 

    public function ClientList()
    {
        try {
            
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $client = Client::where('company_id', $user->id)->get()->map(function( $client){
            return  [
                    'id'=>$client->id,
                    'client_name'=>$client->client_name,
                    'client_contact_number'=>$client->client_contact_number,
                    'client_contact_email'=>$client->client_contact_email,
                    'client_contact_person'=>$client->client_contact_person,
                ];
            });
        
           
            return ResponseBuilder::success($client, 'Client retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

    //add project api 

    public function AddProject(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'project_name' => 'required',
                'start_date' => 'required|date_format:Y-m-d|before_or_equal:end_date',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
                'type_of_project' => 'required',
                'budget' => 'required',
                'location' => 'required',
             
                

            ]);
        
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
    
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
    
          
            $data = [
                'company_id' => $user->id,
                'project_name' => $request->project_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'type_of_project' => $request->type_of_project,
                'budget' => $request->budget,
                'location' => $request->location,
                'description' =>$request->description,
                'region' =>$request->region,
                'client_id' =>$request->client_id,
                'client_manager' =>$request->client_manager,
                'project_director' =>$request->project_director,
                'project_manager' =>$request->project_manager,
               
            ];
    
            
            $project = Project::updateOrCreate(['id' => $request->id], $data);
    
         
            $project->projectEmployees()->sync(json_decode($request->employee_id,true));
    
            if (!empty(json_decode($request->sop_id,true))) {
                ProjectSop::where('project_id', $project->id)->delete();
                foreach (json_decode($request->sop_id,true) as $sopId) {
                    ProjectSop::create([
                        'project_id' => $project->id,
                        'sop_id' => $sopId,
                    ]);
                }
            }
    
            return ResponseBuilder::success($project, 'Project Added/Updated Successfully!');
        } catch (Exception $e) {
            return ResponseBuilder::error($e->getMessage(), $this->serverError);
        }
    }
    


    //sop list api

    public function SopList()
    {
        try {
            
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            //    return $user;
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
         
            $sop = Sop::where('company_id', $user->id)->get()->map(function( $sop){
            return  [
                    'id'=>$sop->id,
                    'title'=>$sop->title,
                    'json_data'=>json_decode( $sop->json_data,true),
                    // 'company_id'=>$sop->company_id,
                    
                    
                ];
            });
        
           
            return ResponseBuilder::success($sop, 'Sop retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

    // get company employee api 


    Public function CompanyEmployee()
    {
        try {
            
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            //    return $user;
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
         
            $employee = Employees::where('company_id', $user->id)->pluck('user_id')->toArray();
        
            $employee = User::whereIn( 'id', $employee )->get();
         
            $this->response = new EmployeesCollection($employee);
           
            return ResponseBuilder::success($this->response , 'Employees retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }
  
    // Add Department Api

    public function AddDepartment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
             
             
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
    
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
             
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $data =[

                'title'=>$request->title,
                'company_id' => $user->id,
            ];
         
            $department = Department::updateOrCreate(['id' => $request->id], $data);

            return ResponseBuilder::success($department ,' Department Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    
    }

    // Add Client api 

    public function AddClient(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_name' => 'required',
                'client_contact_person'=>'required',
                'client_contact_email'=>'required',
             
             
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
    
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            //    return $user;
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $data =[

                'client_name' => $request->client_name,
                'client_contact_person' => $request->client_contact_person,
                'client_contact_email' => $request->client_contact_email,
                'client_contact_number' => $request->client_contact_number,
                'company_id' => $user->id,
            ];
         
            $client = Client::updateOrCreate(['id' => $request->id], $data);

            return ResponseBuilder::success($client ,' Client Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    
    }

    public function AddSop(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'json_data'=>'required',
              
             
             
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
    
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
        //   return  gettype( $request->json_data);
          if(gettype( $request->json_data)!= 'string'){

            $jsonData= json_encode($request->json_data,true);
            
          }
          else{
            $jsonData= $request->json_data;
          }
            $data =[

              
                // 'id'=>$request->id,
                'title'=>$request->title,
                'json_data'=> $jsonData ,
                'company_id' => $user->id,
            ];
              
            $sop = Sop::updateOrCreate(['id' => $request->id], $data);

            return ResponseBuilder::success($sop ,' Sop Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    // Project list api 

    Public function ProjectList()
    {
        try {
            
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $project = Project::where('company_id', $user->id)->get();
                $this->response = new ProjectCollection($project);
        
           
            return ResponseBuilder::success($this->response, 'Project retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

    public function AddCompanyDocument(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'documents_title' => 'required',
             
             
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
    
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            //    return $user;
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $path = 'uploads/companydocument';
            $old_document_file = '';
            $data =[

                'user_id' => $user->id,
                'documents_title' => $request->documents_title,
                'document_file'=> !empty($request->document_file) ? $this->UpdateImage($request->document_file,$path) : 
                $request->old_document_file,
                'who_uploaded' => $request->who_uploaded,
                'date_uploaded' => $request->date_uploaded,
                'validity' => $request->validity,
                'end_date' => $request->end_date,
            ];
         
            $document = CompanyDocument::updateOrCreate(['id' => $request->id], $data);

            return ResponseBuilder::success($document ,' Document Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    // Company document list

    public function CompanyDocumentList()
    {
        try {
            
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            // return $user;
          
            $document = CompanyDocument::where('user_id', $user->id)->get();
            $this->response = new CompanyDocumentCollection($document);
    
       
        return ResponseBuilder::success($this->response, 'Document retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

    // Add employee  document api

    public function AddEmployeeDocument(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'documents_title' => 'required',
             
             
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
    
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            //    return $user;
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $path = 'uploads/company';
            $old_document_file = '';
            $data =[

                'user_id' => $user->id,
                'documents_title' => $request->documents_title,
                'document_file'=> !empty($request->document_file) ? $this->UpdateImage($request->document_file,$path) : 
                $request->old_document_file,
                
            ];
         
            $document = EmployeeDocument::updateOrCreate(['id' => $request->id], $data);

            return ResponseBuilder::success($document ,' Document Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    // employee document list 

    public function employeeDocumentList()
    {
        try {
            
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            // return $user;
          
            $document = EmployeeDocument::where('user_id', $user->id)->get();
            $this->response = new EmployeeDocumentCollection($document);
    
       
        return ResponseBuilder::success($this->response, 'Document retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

    // add project document api

    public function AddProjectDocument(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'documents_title' => 'required',
                'project_id'=>'required',
             
             
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
    
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
          
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $path = 'uploads/project';
            $old_document_file = '';
            $data =[

                'project_id' =>$request->project_id,
                'documents_title' => $request->documents_title,
                'document_file'=> !empty($request->document_file) ? $this->UpdateImage($request->document_file,$path) : 
                $request->old_document_file,
                
            ];
         
            $document = ProjectDocuments ::updateOrCreate(['id' => $request->id], $data);

            return ResponseBuilder::success($document ,' Document Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    //project document list 

    public function projectDocumentList(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'project_id'=>'required',
             
             
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }

            
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            // return $user;
          
            $document = ProjectDocuments::where('project_id',$request->project_id)->get();
          
            $this->response = new ProjectDocumentCollection($document);
    
       
        return ResponseBuilder::success($this->response, 'Document retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

    public function CompanyTimeSheet()
    {
        try {
            if(Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            // return $user;
          
            $timeSheet = TimeSheet::where('company_id',$user->id)->get();
           
            $this->response = new TimeSheetCollection($timeSheet);
            
    
       
        return ResponseBuilder::success(  $this->response, ' Time sheet retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }
   
    //Time sheet status update api

    public function companyStatusUpdate(Request $request)
    {
          try{
               
           
                $validator = Validator::make($request->all(), [
                    'id' => 'required',
                    'status'=>'required',
                 
                 
                ]);
        
                if ($validator->fails()) {
                    return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
                }
        
                if (Auth::guard('api')->check()) {
                    $user = Auth::guard('api')->user();
              
                } else {
                    return ResponseBuilder::error("User not found", $this->unauthorized);
                }
               
                $data = TimeSheet::where('id', $request->id)->first();
                $data->status = $request->status;
                $data->save();
    
                return ResponseBuilder::success($data ,' Status Updated Successfully!',  $this->success);
    
            }    
            catch (\Exception $e) 
            {
        
                return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
          }
    }

    // employee profile update api 

    public function employeeProfileUpdate(Request $request)
    {
        
       
        {
            $validator = Validator::make($request->all(), [
            'company_id' => 'required', 
            // 'employee_photo'=> 'mimes:jpg,jpeg,png',  
            'age' => 'required|integer|min:18',  
            'employee_number' => 'required|integer|min:0', 
            'next_of_kin_contact_number' => 'required|integer|min:0',  
            'telephone_number' => 'required|integer|min:0',               
            'fax_number' => 'required|integer|min:0',               
            
        ]);

        if ($validator->fails()) {   
            return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
        } 
        
        if (!$request->id) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|unique:users',
            ]);

            if ($validator->fails()) {   
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            } 
        }
        


        try{
            $userData = User::where('id',Auth::user()->id)->first();
            if ( $userData->is_employee==false) {
                return ResponseBuilder::error("User not found or is not an employee", $this->notFound);
            }
        $path = 'uploads/employee';
        $oldLogo = '';

            

        $data = [
            'user_id' => $userData->id,
            'company_id' => $request->company_id,
            'first_name' => $request->first_name,
            'surname' => $request->surname,
            'alias_name' => $request->alias_name,
            'suffix' => $request->suffix,
            'gender' => $request->gender,
            'age' => $request->age,
            'dob' => $request->dob,
            'id_number' => $request->id_number,
            'employee_number' => $request->employee_number,
            'hire_date' => $request->hire_date,
            'position' => $request->position,
            'department' => $request->department,
            'office_location' => $request->office_location,
            'desk' => $request->desk,
            'level_in_the_company' => $request->level_in_the_company,
            'salary' => $request->salary,
            'last_date_od_raise' => $request->last_date_od_raise,
            'reporting_manager' => $request->reporting_manager,
            'next_of_kin_name' => $request->next_of_kin_name,
            'next_of_kin_contact_number' => $request->next_of_kin_contact_number,
            'home_address' => $request->home_address,
            'telephone_number' => $request->telephone_number,
            'fax_number' => $request->fax_number,
            'employee_photo' => (!empty($request->file('employee_photo')) ? $this->UpdateImage($request->file('employee_photo'), $path) : null),

        ];

        $employee = Employees::updateOrCreate(['user_id' => $userData->id], $data);


        return ResponseBuilder::successMessage('Employees Profile Update Successfully!',  $this->success);

    }    
    catch (\Exception $e) 
    {

        return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
    }
        }
    }



        // company profile update api 

            public function companyProfileUpdate(Request $request)
            {
                {
                    $validator = Validator::make($request->all(), [
                        'company_name' => 'required',
                        'alias_name' => 'required',
                        'slogan' => 'required',
                        // 'password' => 'required|min:8',
                     
                    ]);
                    $exxitUser = User::where('email',$request->email)->first();
                   
                    if(!empty($exxitUser) && $exxitUser->company_email_verifed == true){
                        return ResponseBuilder::error('The Email has been already taken',  $this->badRequest);
                    }
                  
                    if ($validator->fails()) {   
                        return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
                    } 
                   
                    try{
                       
                        $path = 'uploads/company';
                        $oldLogo = '';
                    
                        if ($request->hasFile('logo')) {
                        
                            $oldLogo = $company->logo ?? null;
                    
                            $request->validate([
                                'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                            ]);
                    
                            $logo = $this->UpdateImage($request->file('logo'), $path);
                        } else {
                            $logo = $request->old_logo;
                        }
                        // $otp = rand(1000,9999);
                        
                        $userData = User::where('id',Auth::user()->id)->first();
                        if ( $userData->is_company==false) {
                            return ResponseBuilder::error("User not found or is not an company", $this->notFound);
                        }
            
                        $data = [
                            'company_name' => $request->company_name,
                            'logo' => $logo,
                            'alias_name' => $request->alias_name,
                            'slogan' => $request->slogan,
                            'full_address' => $request->full_address,
                            'country' => $request->country,
                            'telephone_number' => $request->telephone_number,
                            'website' => $request->website,
                            'subscription_level' => $request->subscription_level,
                            'date_of_registration' => $request->date_of_registration,
                            'contact_person_for_subscription' => $request->contact_person_for_subscription,
                            'contact_email_for_subscription' => $request->contact_email_for_subscription,
                            'contact_number_for_subscription' => $request->contact_number_for_subscription,
                            'no_of_employee' => $request->no_of_employee,
                            'industry' => $request->industry,
                            'vat_number' => $request->vat_number,
                            'tax_number' => $request->tax_number,
                            'registration_number' => $request->registration_number,
                            'b_bbee_level' => $request->b_bbee_level,
                        ];
                    
                        $company = Company::updateOrCreate(['user_id' => $userData->id], $data);
                        return ResponseBuilder::successMessage('Company Profile Update Successfully!',  $this->success);
                        // $mailTemplate = MailTemplate::where('category','verify-company-email')->first();
                    
                        // if($mailTemplate){
                        //     $array1 =  ['{otp}'];
                        //     $array2 = [$otp];  
                        //     $mailTemplate->message = str_replace($array1, $array2, $mailTemplate->message);
                        //     \Mail::to($request->email)->send(new SendMail($mailTemplate));
                        // }
                        // return ResponseBuilder::successMessage('Verification code sent to your email. Please verify',  $this->success);
                }catch (\Exception $e) {
                    return $e;
                    return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
                }
            }
            }

            // get notification api 

            public function getNotification(Request $request)
            {
                try {

                    // $validator = Validator::make($request->all(), [
                    //     'user_id'=>'required',
                     
                     
                    // ]);
            
                    // if ($validator->fails()) {
                    //     return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
                    // }
                    
                    if(Auth::guard('api')->check()) {
                        $user = Auth::guard('api')->user();
                    } else {
                        return ResponseBuilder::error("User not found", $this->unauthorized);
                    }
                 
                    $notification = Notification::where('user_id', $user->id)->get();
                     $this->response = new NotificationCollection($notification);
                
                   
                    return ResponseBuilder::success($this->response, 'Notification retrieved successfully');
                } catch (\Exception $e) {
                    return ResponseBuilder::error($e->getMessage(), 500);
                }
                }
            }
        
      
    




