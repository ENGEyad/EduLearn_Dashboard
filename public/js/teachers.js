document.addEventListener('DOMContentLoaded', () => {
  const ROUTES = window.TEACHERS_ROUTES || {};
  const getListUrl   = ROUTES.list || '/teachers/list';
  const getStoreUrl  = ROUTES.store || '/teachers';
  const getUpdateUrl = ROUTES.update || ((id) => `/teachers/${id}`);
  const getDeleteUrl = ROUTES.destroy || ((id) => `/teachers/${id}`);
  const getImportUrl = ROUTES.import || '/teachers/import';

  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrf = csrfMeta ? csrfMeta.content : '';

  const teachersTableBody = document.querySelector('#teachersTable tbody');
  const teacherSearch = document.getElementById('teacherSearch');
  const teacherSubjectFilter = document.getElementById('teacherSubjectFilter');
  const teacherStatusFilter = document.getElementById('teacherStatusFilter');

  const teachersListView = document.getElementById('teachersListView');
  const teacherFormView = document.getElementById('teacherFormView');
  const openTeacherFormBtn = document.getElementById('openTeacherFormBtn');
  const backToTeachersBtn = document.getElementById('backToTeachersBtn');
  const cancelTeacherBtn = document.getElementById('cancelTeacherBtn');
  const teacherSavedAlert = document.getElementById('teacherSavedAlert');
  const teacherFormTitle = document.getElementById('teacherFormTitle');

  const importTeachersBtn = document.getElementById('importTeachersBtn');
  const importTeachersInput = document.getElementById('importTeachersInput');

  // form fields
  const tcDbId = document.getElementById('tcDbId');
  const tcFullName = document.getElementById('tcFullName');
  const tcBirthPlace = document.getElementById('tcBirthPlace');
  const tcBirthdate = document.getElementById('tcBirthdate');
  const tcAge = document.getElementById('tcAge');
  const calcAgeBtn = document.getElementById('calcAgeBtn');
  const tcQualification = document.getElementById('tcQualification');
  const tcQualificationDate = document.getElementById('tcQualificationDate');
  const tcCurrentSchool = document.getElementById('tcCurrentSchool');
  const tcJoinDate = document.getElementById('tcJoinDate');
  const tcExperienceYears = document.getElementById('tcExperienceYears');
  const tcExperiencePlace = document.getElementById('tcExperiencePlace');
  const tcCurrentRole = document.getElementById('tcCurrentRole');
  const tcWeeklyLoad = document.getElementById('tcWeeklyLoad');
  const tcSalary = document.getElementById('tcSalary');
  const tcShift = document.getElementById('tcShift');
  const tcPhone = document.getElementById('tcPhone');
  const tcNationalId = document.getElementById('tcNationalId');
  const tcMaritalStatus = document.getElementById('tcMaritalStatus');
  const tcChildren = document.getElementById('tcChildren');
  const tcDistrict = document.getElementById('tcDistrict');
  const tcNeighborhood = document.getElementById('tcNeighborhood');
  const tcStreet = document.getElementById('tcStreet');
  const tcStage = document.getElementById('tcStage');
  const tcSubjects = document.getElementById('tcSubjects');
  const tcGrades = document.getElementById('tcGrades');
  const saveTeacherBtn = document.getElementById('saveTeacherBtn');

  // side panel
  const teacherName = document.getElementById('teacherName');
  const teacherId = document.getElementById('teacherId');
  const teacherAvatar = document.getElementById('teacherAvatar');
  const spTcEmail = document.getElementById('spTcEmail');
  const spTcPhone = document.getElementById('spTcPhone');
  const spTcSubjects = document.getElementById('spTcSubjects');
  const spTcClasses = document.getElementById('spTcClasses');
  const spTcAvgScore = document.getElementById('spTcAvgScore');
  const spTcAttendance = document.getElementById('spTcAttendance');
  const spTcAvgScoreBar = document.getElementById('spTcAvgScoreBar');
  const spTcAttendanceBar = document.getElementById('spTcAttendanceBar');
  const spTcAvgScoreVal = document.getElementById('spTcAvgScoreVal');
  const spTcAttendanceVal = document.getElementById('spTcAttendanceVal');

  // tabs
  const tabButtons = document.querySelectorAll('#teacherTabs .nav-link');

  let teachersData = [];
  let currentMode = 'create';

  function clearForm() {
    tcDbId.value = '';
    tcFullName.value = '';
    if (tcBirthPlace) tcBirthPlace.value = '';
    if (tcBirthdate) tcBirthdate.value = '';
    if (tcAge) tcAge.value = '';
    if (tcQualification) tcQualification.value = '';
    if (tcQualificationDate) tcQualificationDate.value = '';
    if (tcCurrentSchool) tcCurrentSchool.value = '';
    if (tcJoinDate) tcJoinDate.value = '';
    if (tcExperienceYears) tcExperienceYears.value = '';
    if (tcExperiencePlace) tcExperiencePlace.value = '';
    if (tcCurrentRole) tcCurrentRole.value = '';
    if (tcWeeklyLoad) tcWeeklyLoad.value = '';
    if (tcSalary) tcSalary.value = '';
    if (tcShift) tcShift.value = '';
    if (tcPhone) tcPhone.value = '';
    if (tcNationalId) tcNationalId.value = '';
    if (tcMaritalStatus) tcMaritalStatus.value = '';
    if (tcChildren) tcChildren.value = 0;
    if (tcDistrict) tcDistrict.value = '';
    if (tcNeighborhood) tcNeighborhood.value = '';
    if (tcStreet) tcStreet.value = '';
    if (tcStage) tcStage.value = '';
    if (tcSubjects) tcSubjects.innerHTML = '';
    if (tcGrades) tcGrades.innerHTML = '';
  }

  function showForm(mode = 'create') {
    currentMode = mode;
    if (mode === 'create') {
      teacherFormTitle.textContent = 'Add New Teacher';
      clearForm();
    } else {
      teacherFormTitle.textContent = 'Edit Teacher';
    }
    teachersListView.style.display = 'none';
    teacherFormView.style.display = 'block';
  }

  function showList() {
    teacherFormView.style.display = 'none';
    teachersListView.style.display = 'block';
    if (teacherSavedAlert) teacherSavedAlert.classList.add('d-none');
  }

  function fillSidePanel(tc) {
    if (!tc) return;
    if (teacherName) teacherName.textContent = tc.full_name || '--';
    if (teacherId) teacherId.textContent = tc.teacher_code || `T-${tc.id}`;
    if (teacherAvatar && tc.full_name) {
      teacherAvatar.textContent = tc.full_name.split(' ').map(p => p[0]).join('').slice(0, 2);
    }
    if (spTcEmail) spTcEmail.textContent = tc.email || '--';
    if (spTcPhone) spTcPhone.textContent = tc.phone || '--';
    if (spTcSubjects) {
      const subs = Array.isArray(tc.subjects) ? tc.subjects.join(', ') : (tc.subjects || '--');
      spTcSubjects.textContent = subs;
    }
    if (spTcClasses) spTcClasses.textContent = tc.students_count ?? 0;
    if (spTcAvgScore) spTcAvgScore.textContent = tc.avg_student_score ? tc.avg_student_score + '%' : '--';
    if (spTcAttendance) spTcAttendance.textContent = tc.attendance_rate ? tc.attendance_rate + '%' : '--';
    if (spTcAvgScoreBar) spTcAvgScoreBar.style.width = (tc.avg_student_score ?? 0) + '%';
    if (spTcAttendanceBar) spTcAttendanceBar.style.width = (tc.attendance_rate ?? 0) + '%';
    if (spTcAvgScoreVal) spTcAvgScoreVal.textContent = (tc.avg_student_score ?? 0) + '%';
    if (spTcAttendanceVal) spTcAttendanceVal.textContent = (tc.attendance_rate ?? 0) + '%';
  }

  function renderTeachers() {
    if (!teachersTableBody) return;
    teachersTableBody.innerHTML = '';

    const search = teacherSearch ? teacherSearch.value.toLowerCase() : '';
    const subjectFilter = teacherSubjectFilter ? teacherSubjectFilter.value.toLowerCase() : '';
    const statusFilter = teacherStatusFilter ? teacherStatusFilter.value : '';

    teachersData
      .filter(tc => {
        const nameMatch = (tc.full_name || '').toLowerCase().includes(search);
        const idMatch = (tc.teacher_code || '').toLowerCase().includes(search);
        return nameMatch || idMatch;
      })
      .filter(tc => {
        if (!subjectFilter) return true;
        if (Array.isArray(tc.subjects)) {
          return tc.subjects.some(s => s.toLowerCase().includes(subjectFilter));
        }
        return (tc.subjects || '').toLowerCase().includes(subjectFilter);
      })
      .filter(tc => {
        if (!statusFilter) return true;
        return (tc.status || '') === statusFilter;
      })
      .forEach(tc => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                ${tc.full_name ? tc.full_name.split(' ').map(p=>p[0]).join('').slice(0,2) : ''}
              </div>
              <div>
                <div>${tc.full_name ?? ''}</div>
                <small class="text-muted">${tc.email ?? ''}</small>
              </div>
            </div>
          </td>
          <td>${tc.teacher_code ?? ''}</td>
          <td>${Array.isArray(tc.subjects) ? tc.subjects.join(', ') : (tc.subjects ?? '')}</td>
          <td>${tc.students_count ?? 0}</td>
          <td>
            <span class="status-pill ${tc.status === 'Active' ? 'status-active' : 'status-inactive'}">${tc.status ?? ''}</span>
          </td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-primary me-1" data-action="edit" data-id="${tc.id}"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" data-action="delete" data-id="${tc.id}"><i class="bi bi-trash"></i></button>
          </td>
        `;

        tr.addEventListener('click', (e) => {
          if (e.target.closest('button')) return;
          fillSidePanel(tc);
        });

        const editBtn = tr.querySelector('button[data-action="edit"]');
        const delBtn = tr.querySelector('button[data-action="delete"]');

        editBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          showForm('edit');
          tcDbId.value = tc.id;
          tcFullName.value = tc.full_name ?? '';
          if (tcBirthPlace) tcBirthPlace.value = tc.birth_governorate ?? '';
          if (tcBirthdate) tcBirthdate.value = tc.birthdate ?? '';
          if (tcAge) tcAge.value = tc.age ?? '';
          if (tcQualification) tcQualification.value = tc.qualification ?? '';
          if (tcQualificationDate) tcQualificationDate.value = tc.qualification_date ?? '';
          if (tcCurrentSchool) tcCurrentSchool.value = tc.current_school ?? '';
          if (tcJoinDate) tcJoinDate.value = tc.join_date ?? '';
          if (tcExperienceYears) tcExperienceYears.value = tc.experience_years ?? '';
          if (tcExperiencePlace) tcExperiencePlace.value = tc.experience_place ?? '';
          if (tcCurrentRole) tcCurrentRole.value = tc.current_role ?? '';
          if (tcWeeklyLoad) tcWeeklyLoad.value = tc.weekly_load ?? '';
          if (tcSalary) tcSalary.value = tc.salary ?? '';
          if (tcShift) tcShift.value = tc.shift ?? '';
          if (tcPhone) tcPhone.value = tc.phone ?? '';
          if (tcNationalId) tcNationalId.value = tc.national_id ?? '';
          if (tcMaritalStatus) tcMaritalStatus.value = tc.marital_status ?? '';
          if (tcChildren) tcChildren.value = tc.children ?? 0;
          if (tcDistrict) tcDistrict.value = tc.district ?? '';
          if (tcNeighborhood) tcNeighborhood.value = tc.neighborhood ?? '';
          if (tcStreet) tcStreet.value = tc.street ?? '';
          if (tcStage) tcStage.value = tc.stage ?? '';

          // refill subjects/grades
          if (tcStage) {
            onStageChange();
          }
          if (Array.isArray(tc.subjects) && tcSubjects) {
            Array.from(tcSubjects.options).forEach(opt => {
              opt.selected = tc.subjects.includes(opt.value);
            });
          }
          if (Array.isArray(tc.grades) && tcGrades) {
            Array.from(tcGrades.options).forEach(opt => {
              opt.selected = tc.grades.includes(opt.value);
            });
          }
        });

        delBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          if (!confirm('Delete this teacher?')) return;
          const url = typeof getDeleteUrl === 'function' ? getDeleteUrl(tc.id) : `/teachers/${tc.id}`;
          fetch(url, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': csrf
            }
          })
          .then(res => res.json())
          .then(() => fetchTeachers())
          .catch(console.error);
        });

        teachersTableBody.appendChild(tr);
      });
  }

  function fetchTeachers() {
    const url = typeof getListUrl === 'function' ? getListUrl() : getListUrl;
    fetch(url)
      .then(res => res.json())
      .then(data => {
        teachersData = data;
        renderTeachers();
        if (teachersData.length) fillSidePanel(teachersData[0]);
      })
      .catch(console.error);
  }
  fetchTeachers();

  if (teacherSearch) teacherSearch.addEventListener('input', renderTeachers);
  if (teacherSubjectFilter) teacherSubjectFilter.addEventListener('change', renderTeachers);
  if (teacherStatusFilter) teacherStatusFilter.addEventListener('change', renderTeachers);

  if (openTeacherFormBtn) openTeacherFormBtn.addEventListener('click', () => showForm('create'));
  if (backToTeachersBtn) backToTeachersBtn.addEventListener('click', showList);
  if (cancelTeacherBtn) cancelTeacherBtn.addEventListener('click', showList);

  // tabs
  const tabHandlers = [];
  tabButtons.forEach(btn => {
    const h = () => {
      tabButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const tab = btn.getAttribute('data-tc-tab');
      document.querySelectorAll('.tc-tab').forEach(t => t.classList.add('d-none'));
      const tabEl = document.getElementById(tab);
      if (tabEl) tabEl.classList.remove('d-none');
    };
    tabHandlers.push({btn, h});
    btn.addEventListener('click', h);
  });

  // calc age
  if (calcAgeBtn) {
    calcAgeBtn.addEventListener('click', () => {
      if (!tcBirthdate || !tcBirthdate.value || !tcAge) return;
      const dob = new Date(tcBirthdate.value);
      const today = new Date();
      let age = today.getFullYear() - dob.getFullYear();
      const m = today.getMonth() - dob.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
      }
      tcAge.value = age;
    });
  }

  // auto-fill subjects/grades
  const basicSubjects = ['Quran','Islamic Education','Science','Mathematics','Social Studies','Arabic','English'];
  const secondarySubjects = ['Quran','Islamic Education','Biology','Chemistry','Physics','Algebra & Geometry','Calculus','Arabic','English'];
  const basicGrades = ['Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8','Grade 9'];
  const secondaryGrades = ['Grade 10','Grade 11','Grade 12'];

  function fillSelectOptions(selectEl, arr) {
    if (!selectEl) return;
    selectEl.innerHTML = '';
    arr.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item;
      opt.textContent = item;
      selectEl.appendChild(opt);
    });
  }

  function onStageChange() {
    if (!tcStage) return;
    if (tcStage.value === 'basic') {
      fillSelectOptions(tcSubjects, basicSubjects);
      fillSelectOptions(tcGrades, basicGrades);
    } else if (tcStage.value === 'secondary') {
      fillSelectOptions(tcSubjects, secondarySubjects);
      fillSelectOptions(tcGrades, secondaryGrades);
    } else {
      if (tcSubjects) tcSubjects.innerHTML = '';
      if (tcGrades) tcGrades.innerHTML = '';
    }
  }

  if (tcStage) tcStage.addEventListener('change', onStageChange);

  // save
  if (saveTeacherBtn) {
    saveTeacherBtn.addEventListener('click', () => {
      const selectedSubjects = tcSubjects ? Array.from(tcSubjects.selectedOptions).map(o => o.value) : [];
      const selectedGrades = tcGrades ? Array.from(tcGrades.selectedOptions).map(o => o.value) : [];

      const payload = {
        full_name: tcFullName.value,
        birth_governorate: tcBirthPlace ? tcBirthPlace.value : '',
        birthdate: tcBirthdate ? tcBirthdate.value : '',
        age: tcAge ? tcAge.value : '',
        qualification: tcQualification ? tcQualification.value : '',
        qualification_date: tcQualificationDate ? tcQualificationDate.value : '',
        current_school: tcCurrentSchool ? tcCurrentSchool.value : '',
        join_date: tcJoinDate ? tcJoinDate.value : '',
        experience_years: tcExperienceYears ? tcExperienceYears.value : '',
        experience_place: tcExperiencePlace ? tcExperiencePlace.value : '',
        current_role: tcCurrentRole ? tcCurrentRole.value : '',
        weekly_load: tcWeeklyLoad ? tcWeeklyLoad.value : '',
        salary: tcSalary ? tcSalary.value : '',
        shift: tcShift ? tcShift.value : '',
        phone: tcPhone ? tcPhone.value : '',
        national_id: tcNationalId ? tcNationalId.value : '',
        marital_status: tcMaritalStatus ? tcMaritalStatus.value : '',
        children: tcChildren ? tcChildren.value : 0,
        district: tcDistrict ? tcDistrict.value : '',
        neighborhood: tcNeighborhood ? tcNeighborhood.value : '',
        street: tcStreet ? tcStreet.value : '',
        stage: tcStage ? tcStage.value : '',
        subjects: selectedSubjects,
        grades: selectedGrades,
        status: 'Active',
      };

      let url = getStoreUrl;
      let method = 'POST';

      if (currentMode === 'edit' && tcDbId.value) {
        if (typeof getUpdateUrl === 'string' && getUpdateUrl.includes('__ID__')) {
          url = getUpdateUrl.replace('__ID__', tcDbId.value);
        } else if (typeof getUpdateUrl === 'function') {
          url = getUpdateUrl(tcDbId.value);
        } else {
          url = `/teachers/${tcDbId.value}`;
        }
        method = 'PUT';
      }

      fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload)
      })
      .then(async (res) => {
        if (!res.ok) {
          const text = await res.text();
          console.error('Server error:', text);
          alert('Saving failed. Status: ' + res.status);
          throw new Error('Request failed');
        }
        return res.json();
      })
      .then(saved => {
        fetchTeachers();
        showList();
        clearForm();
        if (teacherSavedAlert) {
          teacherSavedAlert.textContent = 'Teacher saved successfully.';
          teacherSavedAlert.classList.remove('d-none');
        }
      })
      .catch(console.error);
    });
  }

  // import
  if (importTeachersBtn && importTeachersInput) {
    importTeachersBtn.addEventListener('click', () => importTeachersInput.click());
    importTeachersInput.addEventListener('change', () => {
      const file = importTeachersInput.files[0];
      if (!file) return;

      const fd = new FormData();
      fd.append('file', file);

      const url = typeof getImportUrl === 'function' ? getImportUrl() : getImportUrl;

      fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf
        },
        body: fd
      })
      .then(res => res.json())
      .then(() => {
        fetchTeachers();
      })
      .catch(console.error);
    });
  }

  // cleanup
  window.__pageCleanup = function () {
    // لو بتتنقل بfetch بين الصفحات تقدر تشيل الليستينرز هنا
  };
});
