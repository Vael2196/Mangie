<form action="{{ route('ProjectController.store') }}" method="POST">
    <div class="form-group">
        <label for="project-name">Project Name</label>
        <input type="text" class="form-control" id="project-name" placeholder="Enter project name">
    </div>
    <div class="form-group">
        <label for="project-description">Project Description</label>
        <textarea class="form-control" id="project-description" rows="3"></textarea>
    </div>
    <button type="submit">Submit</button>
</form>