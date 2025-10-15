<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S3 File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">S3 File Manager</h1>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Current Path: {{ $currentPath ?: 'root' }}</h5>
                            <div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                                    <i class="bi bi-folder-plus"></i> Create Folder
                                </button>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                    <i class="bi bi-upload"></i> Upload File
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Last Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($folders as $folder)
                                        <tr>
                                            <td>
                                                <i class="bi bi-folder"></i>
                                                <a href="?path={{ $folder['path'] }}">{{ $folder['name'] }}</a>
                                            </td>
                                            <td>Folder</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td>
                                                <button class="btn btn-danger btn-sm delete-item" data-path="{{ $folder['path'] }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @foreach($files as $file)
                                        <tr>
                                            <td>
                                                <i class="bi bi-file-earmark"></i>
                                                {{ $file['name'] }}
                                            </td>
                                            <td>File</td>
                                            <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                                            <td>{{ $file['last_modified']->format('Y-m-d H:i:s') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($file['visibility'] === 'public')
                                                        <button type="button"
                                                                class="btn btn-info btn-sm copy-public-url"
                                                                data-path="{{ $file['path'] }}"
                                                                title="Copy Public URL">
                                                            <i class="bi bi-clipboard"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button"
                                                            class="btn btn-sm {{ $file['visibility'] === 'public' ? 'btn-success' : 'btn-secondary' }} toggle-visibility"
                                                            data-path="{{ $file['path'] }}"
                                                            data-visibility="{{ $file['visibility'] }}"
                                                            title="Toggle {{ $file['visibility'] === 'public' ? 'Public' : 'Private' }}">
                                                        <i class="bi {{ $file['visibility'] === 'public' ? 'bi-globe' : 'bi-lock' }}"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm delete-item"
                                                            data-path="{{ $file['path'] }}"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div class="modal fade" id="createFolderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('file-manager.create-folder') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="folder_name" class="form-label">Folder Name</label>
                            <input type="text" class="form-control" id="folder_name" name="folder_name" required>
                            <input type="hidden" name="current_path" value="{{ $currentPath }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Folder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div class="modal fade" id="uploadFileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('file-manager.upload-file') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Files</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="files" class="form-label">Select Files (Multiple)</label>
                            <input type="file"
                                   class="form-control"
                                   id="files"
                                   name="files[]"
                                   multiple
                                   required>
                            <input type="hidden" name="current_path" value="{{ $currentPath }}">
                            <div class="form-text">You can select multiple files at once. Max 500MB per file.</div>
                        </div>
                        <div class="mb-3" id="filesList"></div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="visibility" id="visibilityPublic" value="public" checked>
                                <label class="form-check-label" for="visibilityPublic">
                                    <i class="bi bi-globe"></i> Public Access
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="visibility" id="visibilityPrivate" value="private">
                                <label class="form-check-label" for="visibilityPrivate">
                                    <i class="bi bi-lock"></i> Private Access
                                </label>
                            </div>
                        </div>
                        <div class="progress d-none" id="uploadProgress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar"
                                 style="width: 0%"
                                 id="uploadProgressBar">0%</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="uploadButton">
                            <i class="bi bi-upload"></i> Upload Files
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper function to show alerts
            function showAlert(message, type = 'success') {
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show`;
                alert.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.container').insertBefore(alert, document.querySelector('.card'));

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    alert.remove();
                }, 5000);
            }

            // File selection display
            const filesInput = document.getElementById('files');
            const filesList = document.getElementById('filesList');

            filesInput.addEventListener('change', function() {
                filesList.innerHTML = '';
                if (this.files.length > 0) {
                    const list = document.createElement('div');
                    list.className = 'alert alert-info';
                    list.innerHTML = '<strong>Selected files:</strong><ul class="mb-0 mt-2">';

                    Array.from(this.files).forEach(file => {
                        const size = (file.size / 1024 / 1024).toFixed(2);
                        list.innerHTML += `<li>${file.name} (${size} MB)</li>`;
                    });

                    list.innerHTML += '</ul>';
                    filesList.appendChild(list);
                }
            });

            // Upload form with progress
            const uploadForm = document.getElementById('uploadForm');
            const uploadProgress = document.getElementById('uploadProgress');
            const uploadProgressBar = document.getElementById('uploadProgressBar');
            const uploadButton = document.getElementById('uploadButton');

            uploadForm.addEventListener('submit', function(e) {
                const files = filesInput.files;
                if (files.length > 0) {
                    uploadButton.disabled = true;
                    uploadProgress.classList.remove('d-none');

                    // Simulate progress (since we can't track real upload progress with traditional form submit)
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += 5;
                        if (progress <= 90) {
                            uploadProgressBar.style.width = progress + '%';
                            uploadProgressBar.textContent = progress + '%';
                        }
                    }, 200);
                }
            });

            // Delete functionality
            const deleteButtons = document.querySelectorAll('.delete-item');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this item?')) {
                        const path = this.dataset.path;
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route("file-manager.delete-file") }}';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';

                        const pathInput = document.createElement('input');
                        pathInput.type = 'hidden';
                        pathInput.name = 'path';
                        pathInput.value = path;

                        form.appendChild(csrfToken);
                        form.appendChild(pathInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });

            // Copy public URL functionality
            const copyUrlButtons = document.querySelectorAll('.copy-public-url');
            copyUrlButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const path = this.dataset.path;
                    const btn = this;
                    const originalIcon = btn.innerHTML;

                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

                    fetch('{{ route("file-manager.get-public-url") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ path: path })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Use modern clipboard API
                            navigator.clipboard.writeText(data.url).then(() => {
                                btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                                showAlert('Public URL copied to clipboard!', 'success');

                                setTimeout(() => {
                                    btn.innerHTML = originalIcon;
                                    btn.disabled = false;
                                }, 2000);
                            }).catch(() => {
                                // Fallback for older browsers
                                const input = document.createElement('input');
                                input.value = data.url;
                                document.body.appendChild(input);
                                input.select();
                                document.execCommand('copy');
                                document.body.removeChild(input);

                                btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                                showAlert('Public URL copied to clipboard!', 'success');

                                setTimeout(() => {
                                    btn.innerHTML = originalIcon;
                                    btn.disabled = false;
                                }, 2000);
                            });
                        } else {
                            showAlert(data.message || 'Failed to get public URL', 'danger');
                            btn.innerHTML = originalIcon;
                            btn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Error getting public URL', 'danger');
                        btn.innerHTML = originalIcon;
                        btn.disabled = false;
                    });
                });
            });

            // Toggle visibility functionality
            const toggleVisibilityButtons = document.querySelectorAll('.toggle-visibility');
            toggleVisibilityButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const path = this.dataset.path;
                    const currentVisibility = this.dataset.visibility;
                    const newVisibility = currentVisibility === 'public' ? 'private' : 'public';
                    const btn = this;

                    btn.disabled = true;

                    fetch('{{ route("file-manager.update-visibility") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            path: path,
                            visibility: newVisibility
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert(`File visibility changed to ${newVisibility}`, 'success');
                            // Reload page to update UI
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert('Failed to update visibility', 'danger');
                            btn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Error updating visibility', 'danger');
                        btn.disabled = false;
                    });
                });
            });
        });
    </script>
</body>
</html>
