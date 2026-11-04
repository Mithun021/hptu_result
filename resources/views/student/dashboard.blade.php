@extends('student.partial.master')
@section('title', 'Dashboard')
@section('student-content')

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <center>
                    @php
                        $profileImage = auth('student')->user()->profile_image;
                        $imagePath = public_path($profileImage);
                    @endphp

                    <img 
                        src="{{ (!empty($profileImage) && file_exists($imagePath)) 
                                ? asset($profileImage) 
                                : asset('assets/images/users/blank-profile-picture.webp') }}" 
                        class="img-thumbnail rounded-circle avatar-xl img-fluid" 
                        alt="Profile Image">

                    <h4 class="mt-2 mb-0">{{ auth('student')->user()->student_name ?? 'Student' }}</h4>
                </center>
                <hr>
                <h6 class="m-0">University Roll no. : </h6>
                <p class="text-primary m-0">{{ auth('student')->user()->university_roll_no ?? '' }}</p>
                <hr>
                <h6 class="m-0">Course Name : </h6>
                <p class="text-primary m-0">
                    {{ auth('student')->user()->course->name ?? '' }}
                    
                    @if(!empty(auth('student')->user()->branch->name) && auth('student')->user()->branch->name !== 'NA')
                        ({{ auth('student')->user()->branch->name }})
                    @endif
                </p>
                <hr>
                <h6 class="m-0">College Name : </h6>
                <p class="text-primary m-0">{{ auth('student')->user()->institute->name ?? '' }}</p>

                <hr>
                @php
                    $user = auth('student')->user();
                    $courseType = $user->course_type ?? '';
                @endphp

                @if($courseType === 'Semester Based')
                    <h6 class="m-0">Semester : <span class="text-primary m-0">{{ $user->semester->name ?? '' }}</span></h6>
                @elseif($courseType === 'Annual Based')
                    <h6 class="m-0">Annual Terms : <span class="text-primary m-0">{{ $user->annual_semester->name ?? '' }}</span></h6>
                @endif
                
            </div>
        </div>
    </div>
    <div class="col-md-8">

        @php
            $user = auth('student')->user();

            $profileImage = $user->profile_image;
            $signature = $user->signature;

            $profileImageExists = !empty($profileImage) && file_exists(public_path($profileImage));
            $signatureExists = !empty($signature) && file_exists(public_path($signature));
        @endphp

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Profile Information</h4>

                <div>
                    {{-- Edit Profile Link always visible --}}
                    <a href="javascript:void(0);" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        Edit Profile
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Show warning only if any is missing --}}
                @if(!$profileImageExists || !$signatureExists)
                    <div class="px-3 pb-2">
                        <div class="alert alert-danger d-flex align-items-center mb-0" role="alert">
                            <i class="mdi mdi-alert-circle-outline me-2"></i>
                            
                            <div>
                                <strong>Incomplete Profile!</strong><br>
                                Please upload 
                                @if(!$profileImageExists && !$signatureExists)
                                    your profile image and signature.
                                @elseif(!$profileImageExists)
                                    your profile image.
                                @else
                                    your signature.
                                @endif
                            </div>
                        </div>
                    </div>
                @endif


                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <td width="50%">Father's Name</td>
                            <td>
                                {{ $user->father_name ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">Mother's Name</td>
                            <td>{{ $user->mother_name ?? '' }}</td>
                        </tr>
                        <tr>
                            <td width="50%">Date of Birth</td>
                            <td>{{ $user->dob ? $user->dob->format('j-M-Y') : '' }}</td>
                        </tr>
                        <tr>
                            <td width="50%">Gender</td>
                            <td>{{ $user->gender ?? '' }}</td>
                        </tr>
                        <tr>
                            <td width="50%">Contact Number</td>
                            <td>{{ $user->phone ?? '' }}</td>
                        </tr>
                        <tr>
                            <td width="50%">Email</td>
                            <td>{{ $user->email ?? '' }}</td>
                        </tr>
                        <tr>
                            <td width="50%">ABC Id</td>
                            <td>{{ $user->abcid ?? '' }}</td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title m-0">Address Information</h4>
            </div>
            <div class="card-body">
                <p><strong>Current Address:</strong> <br> <span class="text-primary">{{ $user->current_address ?? '' }}</span></p>
                <p><strong>Permanent Address:</strong> <br> <span class="text-primary">{{ $user->parmanent_address ?? '' }}</span></p>
            </div>
        </div>

    </div>

</div>


<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Profile Image</label>
                            <input type="file" name="profile_image" class="form-control">
                            @if(!empty($user->profile_image) && file_exists(public_path($user->profile_image)))
                                <img src="{{ asset($user->profile_image) }}" 
                                    class="img-thumbnail mb-2" 
                                    style="height:80px; width:80px; object-fit:cover;">
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Signature</label>
                            <input type="file" name="signature" class="form-control">
                            @if(!empty($user->signature) && file_exists(public_path($user->signature)))
                                <img src="{{ asset($user->signature) }}" 
                                    class="img-thumbnail mb-2" 
                                    style="height:60px; object-fit:contain;">
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Father's Name</label>
                            <input type="text" name="father_name" value="{{ $user->father_name ?? '' }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Mother's Name</label>
                            <input type="text" name="mother_name" value="{{ $user->mother_name ?? '' }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>ABC ID</label>
                            <input type="text" name="abcid" value="{{ $user->abcid ?? '' }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Mobile No</label>
                            <input type="text" name="phone" value="{{ $user->phone ?? '' }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email ID</label>
                            <input type="email" name="email" value="{{ $user->email ?? '' }}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>DOB</label>
                            <input type="date" name="dob" value="{{ $user->dob ? $user->dob->format('Y-m-d') : '' }}" class="form-control">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Current Address</label>
                            <textarea name="current_address" class="form-control">{{ $user->current_address ?? '' }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Permanent Address</label>
                            <textarea name="parmanent_address" class="form-control">{{ $user->parmanent_address ?? '' }}</textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
