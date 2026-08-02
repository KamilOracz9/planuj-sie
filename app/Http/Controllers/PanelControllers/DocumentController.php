<?php

namespace App\Http\Controllers\PanelControllers;

class DocumentController extends BaseGalleryController
{
    protected string $collection = 'documents';

    protected array $fileRules = ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv', 'max:20480'];
}
