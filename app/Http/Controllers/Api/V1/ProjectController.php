<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helper\ResponseBuilder;
use App\Http\Resources\ProjectCollection;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\EmployeesCollection;
use App\Http\Resources\ProjectTaskCollection;
use App\Http\Resources\ProjectTaskResource;
use App\Http\Resources\ProjectExpenseCollection;
use App\Http\Resources\ProjectExpenseResource;
use App\Http\Resources\ProjectCommentCollection;
use App\Http\Resources\ProjectCommentResource;
use App\Http\Resources\TaskCommentCollection;
use App\Http\Resources\TaskCommentResource;
use App\ProjectTask;
use App\ProjectComment;
use App\ProjectExpense;
use App\Project;
use App\TaskComment;
use Validator;
use Carbon\Carbon;
use Exception;
use Hash;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
       
    // add project task api 
    public function AddProjectTask(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'task_manager' => 'required',
            'start_date' => 'required|before_or_equal:end_date',
            'end_date' => 'required|after_or_equal:start_date',
             
             
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

                'project_id' => $request->project_id,
                'ref_number' => $request->ref_number,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'task_manager' => $request->task_manager,
                'phase' => $request->phase,
                'category' => $request->category,
                'status' => 'pending',
           
                
            ];
         
            $project = ProjectTask ::updateOrCreate(['id' => $request->id], $data);
            $project->taskEmployees()->sync(json_decode($request->employee_id,true));

            return ResponseBuilder::success($project ,' Project Task Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    //project task list 

    public function projectTaskList(Request $request)
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
          
            $document = ProjectTask::where('project_id',$request->project_id)->get();
          
            $this->response = new ProjectTaskCollection($document);
    
       
        return ResponseBuilder::success($this->response, 'Project Task retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }


    //  add project expense api 

    public function AddProjectExpense(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'date' => 'required',
            'amount'=>'required',
            'receipt_file'=>'required',
           
             
            ]);
    
            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
            }
    
            if (Auth::guard('api')->check()) {
                $user = Auth::guard('api')->user();
          
            } else {
                return ResponseBuilder::error("User not found", $this->unauthorized);
            }
            $path = 'uploads/expense';
            $data =[

                'project_id' => $request->project_id,
                'expense_type' => $request->expense_type,
                'date' => $request->date,
                'amount' => $request->amount,
                'status' => 'pending',
                'description' => $request->description,
                'receipt_file' => !empty($request->receipt_file) ? $this->UpdateImage($request->receipt_file, $path) : $request->old_receipt_file,
           
                
            ];
         
            $project = ProjectExpense ::updateOrCreate(['id' => $request->id], $data);
           

            return ResponseBuilder::success($project ,' Project Expense Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
    }

    //project expense list 

    public function ProjectExpenseList(Request $request)
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
          
            $document = ProjectExpense::where('project_id',$request->project_id)->get();
          
            $this->response = new ProjectExpenseCollection($document);
    
       
        return ResponseBuilder::success($this->response, 'Project Expense retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }
    
    // project details api 

    public function ProjectDetails(Request $request)
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
            $project = Project::where('id', $request->project_id)->first();
                $this->response = new ProjectResource($project);
        
           
            return ResponseBuilder::success($this->response, 'Project details retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

    //Task Details api 

    public function TaskDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id'=>'required',
             
             
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
          
            $document = ProjectTask::where('id',$request->id)->first();
          
            $this->response = new ProjectTaskResource($document);
    
       
        return ResponseBuilder::success($this->response, ' Task  details retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }
    }

     // project comment api

     public function projectComment(Request $request)
     {
        try {
            $validator = Validator::make($request->all(), [
            'project_id' => 'required',
            'comment' => 'required',
          
           
             
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
                 'user_id'=> $user->id,
                 'project_id' => $request->project_id,
                 'comment'=> $request->comment,        
                
            ];
         
            $comment = ProjectComment ::updateOrCreate(['id' => $request->id], $data);
           

            return ResponseBuilder::success($comment ,' Project Comment Add Successfully!',  $this->success);

        }    
        catch (\Exception $e) 
        {
    
            return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
        }
     }

     // get project comment api 

     public function projectCommentList(Request $request)
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
            $comment = ProjectComment::where('user_id', $user->id)->get();
             $this->response = new ProjectCommentCollection($comment);
        
           
            return ResponseBuilder::success($this->response, 'Project Comment retrieved successfully');
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), 500);
        }

     }

     // add task comment api 

     public function taskComment(Request $request)
     {
        try{
            $validator = Validator::make($request->all(), [
                'task_id' => 'required',
                'comment' => 'required',
              
               
                 
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
                     'user_id'=> $user->id,
                     'task_id' => $request->task_id,
                     'comment'=> $request->comment,        
                    
                ];
             
                $comment = TaskComment ::updateOrCreate(['id' => $request->id], $data);
               
    
                return ResponseBuilder::success($comment ,' Task Comment Add Successfully!',  $this->success);
    
            }    
            catch (\Exception $e) 
            {
        
                return ResponseBuilder::error(__($e->getMessage()), $this->serverError);
            }

        }

        // get task comment

        public function taskCommentList(Request $request)
        {
            try {

                $validator = Validator::make($request->all(), [
                    'task_id'=>'required',
                 
                 
                ]);
        
                if ($validator->fails()) {
                    return ResponseBuilder::error($validator->errors()->first(), $this->badRequest);
                }
                
                if(Auth::guard('api')->check()) {
                    $user = Auth::guard('api')->user();
                } else {
                    return ResponseBuilder::error("User not found", $this->unauthorized);
                }
                $comment = TaskComment::where('user_id', $user->id)->get();
                 $this->response = new TaskCommentCollection($comment);
            
               
                return ResponseBuilder::success($this->response, 'Task Comment retrieved successfully');
            } catch (\Exception $e) {
                return ResponseBuilder::error($e->getMessage(), 500);
            }
            }
        }
     

 

