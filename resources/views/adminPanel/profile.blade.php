@extends('adminPanel.layout.app')
@section('content')
@if( isset($adminDataProfile) )
<main class="app-content">
   <div class="row admin">
      <div class="col-md-12">
         <div class="profiles">
            <div class="cover-image"></div>
         </div>
      </div>
      <div class="col-md-2"></div>
      <div class="col-md-8" style="margin-top: -3%;">
         <div class="tab-content">
            <div class="tab-pane  active" id="user-settings">
               <div class="tile user-settings">
                  <h4 class="line-head">{{__('adminPanel.Settings')}}</h4>
                  <form method="POST" action="{{ route('vUpdateProfile') }}" enctype="multipart/form-data">
                  {{csrf_field()}}
                     <div class="row mb-12">
                       <div class="col-md-4 border-5px">
                       <div class="col-md-12 mb-4">
                            <div class="profiles">
                              <div class="infos">
                                 @if(isset($adminDataProfile[0]->profileImage))
                                    <img class="user-img" src="{{asset($adminDataProfile[0]->profileImage)}}">
                                 @else
                                    <i class="fa fa-user-circle" style="font-size: 100px;margin-right: 5px;" aria-hidden="true"></i>
                                 @endif
                                 
                                <h4>{{$adminDataProfile[0]->firstName}} {{$adminDataProfile[0]->lastName}}</h3>
                              </div>
                          </div>
                           </div>
                           <div class="col-md-12 mb-4">
                              <label>{{__('adminPanel.SelectImageProfile')}} </label>
                              <input class="form-control" name="profileImage" type="file"accept = "image/jpeg , image/jpg, image/gif, image/png">
                           </div>
                        </div> 
                         <div class="col-md-8 border-5px">
                           <div class="row mb-12">
                              <div class="col-md-6">
                                 <label>{{__('adminPanel.FirstName')}}</label>
                                 <input class="form-control" type="text" name="firstName" value="{{$adminDataProfile[0]->firstName}}">
                              </div>
                              <div class="col-md-6">
                                 <label>{{__('adminPanel.LastName')}}</label>
                                 <input class="form-control" type="text" name="lastName" value="{{$adminDataProfile[0]->lastName}}">
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-12 mb-4">
                                 <label>{{__('adminPanel.Email')}}</label>
                                 <input class="form-control" type="text" value=" {{$adminDataProfile[0]->emailID}}" readonly>
                              </div>
                              <div class="clearfix"></div>
                              <div class="col-md-12 mb-4">
                                 <label>{{__('adminPanel.Username')}}</label>
                                 <input class="form-control" type="text" value=" {{$adminDataProfile[0]->username}}" readonly>
                              </div>
                              <div class="clearfix"></div>
                              <div class="col-md-12 mb-4">
                                 <label>{{__('adminPanel.lastTimeResetPassword')}}  </label>
                                 <input class="form-control" type="text" value=" {{$adminDataProfile[0]->lastPasswordResetTime}}" readonly>
                              </div>
                              <div class="clearfix"></div>
                           </div>
                        </div>
                        <div class="row col-md-12">
                              <div class="col-md-4">
                                
                              </div>
                              <div class="col-md-8">
                                 <button class="btn btn-primary btn-lg mt-2 btn-block-20" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i> {{__('adminPanel.Save')}}</button>
                              </div>
                           </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-2"></div>
</main>
<style>
   .editprofile{
   }
   .border-5px{
   border: 1px solid #ccc;
   border-radius: 5px;
   padding: 10px 5px;
   }
</style>
@endif
@endsection