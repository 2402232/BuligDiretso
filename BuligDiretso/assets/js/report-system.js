// report_emergency.js

// Emergency type selection
document.querySelectorAll('.emergency-box').forEach(function(box) {
  box.addEventListener('click', function() {
    document.querySelectorAll('.emergency-box').forEach(function(b) {
      b.classList.remove('selected');
    });
    this.classList.add('selected');
    document.getElementById('emergency_type').value = this.getAttribute('data-type');
  });
});

// File upload — show selected filename
document.getElementById('file_upload').addEventListener('change', function() {
  if (this.files.length > 0) {
    var p = document.querySelector('.upload-area p');
    p.textContent = 'Selected: ' + this.files[0].name;
    p.style.color = '#16A34A';
  }
});

// Form validation before submit
document.querySelector('form').addEventListener('submit', function(e) {
  if (!document.getElementById('emergency_type').value) {
    e.preventDefault();
    alert('Please select an emergency type!');
  }
});