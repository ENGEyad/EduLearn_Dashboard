@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3" id="teachersHeader">
  <div>
    <h5 class="mb-0">Teachers Management</h5>
    <small class="text-muted">Add, edit and monitor teachers</small>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary" id="importTeachersBtn"><i class="bi bi-upload"></i> Bulk Import</button>
    <button class="btn btn-primary" id="openTeacherFormBtn">
      <i class="bi bi-plus"></i> Add New Teacher
    </button>
  </div>
</div>

<!-- list view -->
<div id="teachersListView">
  <div class="table-shell mb-3">
    <div class="row g-2 align-items-center mb-2">
      <div class="col-md-4">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
          <input type="text" id="teacherSearch" class="form-control border-start-0" placeholder="Search by name or ID..." />
        </div>
      </div>
      <div class="col-md-3">
        <select class="form-select" id="teacherSubjectFilter">
          <option value="">Filter by Subject</option>
          <option>Mathematics</option>
          <option>History</option>
          <option>Biology</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" id="teacherStatusFilter">
          <option value="">Filter by Status</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
    </div>
    <table class="table align-middle" id="teachersTable">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Teacher ID</th>
          <th>Subjects Taught</th>
          <th>Students</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <div class="profile-shell" id="teacherProfile">
    <div class="profile-header">
      <div class="avatar-circle" id="teacherAvatar">OR</div>
      <div>
        <h6 class="profile-name" id="teacherName">--</h6>
        <div class="profile-meta" id="teacherId">--</div>
      </div>
    </div>

    <ul class="nav nav-tabs small mb-3 profile-tabs" id="teacherTabs">
      <li class="nav-item"><button class="nav-link active" data-tc-tab="tc-personal">Personal</button></li>
      <li class="nav-item"><button class="nav-link" data-tc-tab="tc-academic">Academic</button></li>
      <li class="nav-item"><button class="nav-link" data-tc-tab="tc-performance">Performance</button></li>
    </ul>

    <div id="tc-personal" class="tc-tab">
      <div class="kv-row"><span class="kv-label">Email</span><span class="kv-value" id="spTcEmail">--</span></div>
      <div class="kv-row"><span class="kv-label">Phone</span><span class="kv-value" id="spTcPhone">--</span></div>
    </div>
    <div id="tc-academic" class="tc-tab d-none">
      <div class="kv-row"><span class="kv-label">Subjects</span><span class="kv-value" id="spTcSubjects">--</span></div>
      <div class="kv-row"><span class="kv-label">Assigned Classes</span><span class="kv-value" id="spTcClasses">--</span></div>
    </div>
    <div id="tc-performance" class="tc-tab d-none">
      <div class="kv-row"><span class="kv-label">Avg. Student Score</span><span class="kv-value" id="spTcAvgScore">--</span></div>
      <div class="kv-row"><span class="kv-label">Attendance</span><span class="kv-value" id="spTcAttendance">--</span></div>
    </div>

    <hr class="my-3" />
    <div class="metrics-title mb-2">Key Metrics</div>
    <div class="metric-line">
      <div class="metric-label">Avg. Student Score</div>
      <div class="progress-compact flex-grow-1"><span id="spTcAvgScoreBar" style="width:0%"></span></div>
      <div class="metric-value" id="spTcAvgScoreVal">0%</div>
    </div>
    <div class="metric-line">
      <div class="metric-label">Attendance</div>
      <div class="progress-compact flex-grow-1"><span id="spTcAttendanceBar" style="width:0%"></span></div>
      <div class="metric-value" id="spTcAttendanceVal">0%</div>
    </div>
    <p class="text-muted small mt-2 mb-0">Data here is read-only and shown for review purposes.</p>
  </div>
</div>

<!-- form view -->
<div id="teacherFormView" class="card-panel" style="display:none;">
  <input type="hidden" id="tcDbId" value="">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h5 class="mb-1" id="teacherFormTitle">Add New Teacher</h5>
      <small class="text-muted">Enter all personal, academic and teaching details</small>
    </div>
    <button class="btn btn-outline-secondary btn-sm" id="backToTeachersBtn">
      <i class="bi bi-arrow-right"></i> Back to Teachers
    </button>
  </div>

  {{-- Personal Information --}}
  <h6 class="mb-2">Personal Information</h6>
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <label class="form-label">Full Name</label>
      <input type="text" class="form-control" id="tcFullName" placeholder="e.g. Yasser Abdullah Hassan" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Place of Birth (Governorate)</label>
      <select class="form-select" id="tcBirthPlace">
        <option value="">Select governorate</option>
        <option>Aden</option>
        <option>Taiz</option>
        <option>Sana'a</option>
        <option>Hadhramaut</option>
        <option>Al-Hodeidah</option>
        <option>Ibb</option>
        <option>Dhamar</option>
        <option>Raymah</option>
        <option>Al-Jawf</option>
        <option>Saada</option>
        <option>Amanat Al-Asimah</option>
        <option value="other">Other...</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Date of Birth</label>
      <input type="date" class="form-control" id="tcBirthdate">
    </div>
    <div class="col-md-2">
      <label class="form-label">Age</label>
      <div class="input-group">
        <input type="text" class="form-control" id="tcAge" readonly>
        <button class="btn btn-outline-secondary" type="button" id="calcAgeBtn">Calc</button>
      </div>
    </div>
  </div>

  <h6 class="mb-2 mt-4">Academic & Job Details</h6>
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <label class="form-label">Qualification</label>
      <select class="form-select" id="tcQualification">
        <option value="">Select</option>
        <option>Secondary</option>
        <option>Diploma</option>
        <option>Bachelor</option>
        <option>Master</option>
        <option>PhD</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Qualification Date</label>
      <input type="date" class="form-control" id="tcQualificationDate">
    </div>
    <div class="col-md-3">
      <label class="form-label">Current School</label>
      <input type="text" class="form-control" id="tcCurrentSchool" placeholder="e.g. Al-Noor School">
    </div>
    <div class="col-md-3">
      <label class="form-label">Join Date</label>
      <input type="date" class="form-control" id="tcJoinDate">
    </div>
  </div>

  {{-- خبرة واحدة فقط --}}
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <label class="form-label">Experience Years</label>
      <input type="number" class="form-control" id="tcExperienceYears" placeholder="e.g. 5">
    </div>
    <div class="col-md-5">
      <label class="form-label">Experience Place</label>
      <input type="text" class="form-control" id="tcExperiencePlace" placeholder="School / Place">
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <label class="form-label">Current Role / Position</label>
      <select class="form-select" id="tcCurrentRole">
        <option value="">Select</option>
        <option>Teacher</option>
        <option>Supervisor</option>
        <option>Control / Exams</option>
        <option>Assistant Principal</option>
        <option>Principal</option>
        <option>IT / Technical Support</option>
        <option>Other</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Weekly Teaching Load (periods)</label>
      <input type="number" class="form-control" id="tcWeeklyLoad" placeholder="e.g. 24">
    </div>
    <div class="col-md-4">
      <label class="form-label">Monthly Salary</label>
      <input type="number" class="form-control" id="tcSalary" placeholder="e.g. 120000">
    </div>
  </div>

  <h6 class="mb-2 mt-4">Duty & Attendance</h6>
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <label class="form-label">Shift</label>
      <select class="form-select" id="tcShift">
        <option value="">Select</option>
        <option>Morning</option>
        <option>Evening</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Teacher Phone</label>
      <input type="text" class="form-control" id="tcPhone" placeholder="77xxxxxxx">
    </div>
    <div class="col-md-3">
      <label class="form-label">National ID</label>
      <input type="text" class="form-control" id="tcNationalId" placeholder="ID number">
    </div>
  </div>

  <h6 class="mb-2 mt-4">Social & Address</h6>
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <label class="form-label">Marital Status</label>
      <select class="form-select" id="tcMaritalStatus">
        <option value="">Select</option>
        <option>Single</option>
        <option>Married</option>
        <option>Divorced</option>
        <option>Widowed</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Children</label>
      <input type="number" class="form-control" id="tcChildren" min="0" value="0">
    </div>
    <div class="col-md-3">
      <label class="form-label">District</label>
      <input type="text" class="form-control" id="tcDistrict" placeholder="District">
    </div>
    <div class="col-md-2">
      <label class="form-label">Neighborhood</label>
      <input type="text" class="form-control" id="tcNeighborhood" placeholder="Neighborhood">
    </div>
    <div class="col-md-2">
      <label class="form-label">Block / Street</label>
      <input type="text" class="form-control" id="tcStreet" placeholder="Street">
    </div>
  </div>

  <h6 class="mb-2 mt-4">Teaching Assignment</h6>
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <label class="form-label">Stage</label>
      <select class="form-select" id="tcStage">
        <option value="">Select stage</option>
        <option value="basic">Basic (Primary)</option>
        <option value="secondary">Secondary</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Subjects (auto-filled)</label>
      <select class="form-select" id="tcSubjects" multiple></select>
      <small class="text-muted small">Ctrl/Cmd + click to select multiple</small>
    </div>
    <div class="col-md-3">
      <label class="form-label">Grades (auto-filled)</label>
      <select class="form-select" id="tcGrades" multiple></select>
    </div>
  </div>

  <div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="button" id="saveTeacherBtn">
      <i class="bi bi-check2"></i> Save Teacher
    </button>
    <button class="btn btn-light" type="button" id="cancelTeacherBtn">Cancel</button>
  </div>

  <div class="alert alert-success mt-3 d-none" id="teacherSavedAlert"></div>
</div>

<input type="file" id="importTeachersInput" class="d-none" />

@endsection

@push('scripts')
<script>
  window.TEACHERS_ROUTES = {
    list: @json($TEACHERS_ROUTES['list']),
    store: @json($TEACHERS_ROUTES['store']),
    update: @json($TEACHERS_ROUTES['update']),
    destroy: @json($TEACHERS_ROUTES['destroy']),
    import: @json($TEACHERS_ROUTES['import']),
  };
</script>
<script src="{{ asset('js/teachers.js') }}"></script>
@endpush
