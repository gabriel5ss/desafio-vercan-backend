<div class="col-12">
    <label for="observation" class="form-label">Observação</label>
    <textarea id="observation" name="observation" class="form-control @error('observation') is-invalid @enderror" rows="4" maxlength="5000" aria-describedby="observation-help">{{ old('observation', $user->observation?->content) }}</textarea>
    <div id="observation-help" class="form-text">Campo opcional. Deixe em branco para remover a observação.</div>
    @error('observation')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
