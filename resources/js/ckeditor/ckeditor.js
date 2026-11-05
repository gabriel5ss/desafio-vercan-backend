import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Heading,
    Bold,
    Italic,
    Link,
    List,
    BlockQuote,
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';
import ptBR from 'ckeditor5/translations/pt-br.js';

export function initializeEditors() {
    $('textarea[data-ckeditor]').each(function () {
        ClassicEditor.create({
            attachTo: this,
            licenseKey: 'GPL',
            language: 'pt-br',
            translations: [ptBR],
            plugins: [Essentials, Paragraph, Heading, Bold, Italic, Link, List, BlockQuote],
            toolbar: [
                'undo', 'redo', '|', 'heading', '|',
                'bold', 'italic', 'link', '|',
                'bulletedList', 'numberedList', 'blockQuote',
            ],
        })
            .then((editor) => {
                if (this.matches(':disabled')) {
                    editor.enableReadOnlyMode('disabled-form');
                    editor.ui.view.toolbar.element.hidden = true;
                }
            })
            .catch(function (error) {
                console.error('Erro ao inicializar CKEditor:', error);
            });
    });
}
