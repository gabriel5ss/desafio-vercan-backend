<x-form.editor
    id="supplier-observation"
    name="observation[content]"
    error-name="observation.content"
    :value="$formData['observation']['content'] ?? ''"
    rows="6"
    placeholder="Digite as observações do fornecedor..."
/>

@error('observation')
    <div class="text-danger small mt-1">{{ $message }}</div>
@enderror
