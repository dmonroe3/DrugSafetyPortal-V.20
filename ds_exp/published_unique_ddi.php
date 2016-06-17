<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *                                   ATTENTION!
 * If you see this message in your browser (Internet Explorer, Mozilla Firefox, Google Chrome, etc.)
 * this means that PHP is not properly installed on your web server. Please refer to the PHP manual
 * for more details: http://php.net/manual/install.php 
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */


    include_once dirname(__FILE__) . '/' . 'components/utils/check_utils.php';
    CheckPHPVersion();
    CheckTemplatesCacheFolderIsExistsAndWritable();


    include_once dirname(__FILE__) . '/' . 'phpgen_settings.php';
    include_once dirname(__FILE__) . '/' . 'database_engine/mysql_engine.php';
    include_once dirname(__FILE__) . '/' . 'components/page.php';
    include_once dirname(__FILE__) . '/' . 'authorization.php';

    function GetConnectionOptions()
    {
        $result = GetGlobalConnectionOptions();
        $result['client_encoding'] = 'utf8';
        GetApplication()->GetUserAuthorizationStrategy()->ApplyIdentityToConnectionOptions($result);
        return $result;
    }

    
    // OnGlobalBeforePageExecute event handler
    
    
    // OnBeforePageExecute event handler
    
    
    
    class published_unique_ddiPage extends Page
    {
        protected function DoBeforeCreate()
        {
            $this->dataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`published_unique_ddi`');
            $field = new IntegerField('published_ddi_id', null, null, true);
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, true);
            $field = new IntegerField('acceptance_venue');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('paper_title');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('unique_ddi_identifier');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('pubmed_id');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('DOI');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('drug1');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('drug2');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('event');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new IntegerField('drug_vocabulary');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new IntegerField('event_vocabulary');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $this->dataset->AddLookupField('drug_vocabulary', 'drug_vocabularies', new IntegerField('drug_vocab_id', null, null, true), new StringField('vocab_name', 'drug_vocabulary_vocab_name', 'drug_vocabulary_vocab_name_drug_vocabularies'), 'drug_vocabulary_vocab_name_drug_vocabularies');
            $this->dataset->AddLookupField('event_vocabulary', 'event_vocabularies', new IntegerField('event_vocab_id', null, null, true), new StringField('event_vocab_name', 'event_vocabulary_event_vocab_name', 'event_vocabulary_event_vocab_name_event_vocabularies'), 'event_vocabulary_event_vocab_name_event_vocabularies');
            $this->dataset->AddLookupField('acceptance_venue', 'journals', new IntegerField('journal_id', null, null, true), new StringField('journal_title', 'acceptance_venue_journal_title', 'acceptance_venue_journal_title_journals'), 'acceptance_venue_journal_title_journals');
        }
    
        protected function DoPrepare() {
    
        }
    
        protected function CreatePageNavigator()
        {
            $result = new CompositePageNavigator($this);
            
            $partitionNavigator = new PageNavigator('pnav', $this, $this->dataset);
            $partitionNavigator->SetRowsPerPage(20);
            $result->AddPageNavigator($partitionNavigator);
            
            return $result;
        }
    
        protected function CreateRssGenerator()
        {
            return null;
        }
    
        protected function setupCharts()
        {
            $sql = 'SELECT A.ddi, count(A.ddi) AS appearances FROM (SELECT CONCAT(drug1,\'-\',drug2,\'-\',event) AS ddi, published_ddi_id FROM published_unique_ddi) as A GROUP BY A.ddi ORDER BY A.published_ddi_id DESC LIMIT 50';
            
            $chart = new Chart('Chart01', Chart::TYPE_PIE, $this->dataset, $sql);
            $chart->setTitle($this->RenderText('Distinct DDI\'s in Drug Safety Portal'));
            $chart->setDomainColumn('ddi', $this->RenderText('DDI'), 'string');
            $chart->addDataColumn('appearances', $this->RenderText('Frequency'), 'int');
            $this->addChart($chart, 0, ChartPosition::BEFORE_GRID, 12);
        }
    
        protected function CreateGridSearchControl(Grid $grid)
        {
            $grid->UseFilter = true;
            $grid->SearchControl = new SimpleSearch('published_unique_ddissearch', $this->dataset,
                array('published_ddi_id', 'drug1', 'drug2', 'event', 'drug_vocabulary_vocab_name', 'event_vocabulary_event_vocab_name', 'acceptance_venue_journal_title', 'paper_title', 'pubmed_id', 'DOI', 'unique_ddi_identifier'),
                array($this->RenderText('Published Ddi Id'), $this->RenderText('Drug1'), $this->RenderText('Drug2'), $this->RenderText('Event'), $this->RenderText('Drug Vocabulary'), $this->RenderText('Event Vocabulary'), $this->RenderText('Acceptance Venue'), $this->RenderText('Paper Title'), $this->RenderText('Pubmed Id'), $this->RenderText('DOI'), $this->RenderText('Unique Ddi Identifier')),
                array(
                    '=' => $this->GetLocalizerCaptions()->GetMessageString('equals'),
                    '<>' => $this->GetLocalizerCaptions()->GetMessageString('doesNotEquals'),
                    '<' => $this->GetLocalizerCaptions()->GetMessageString('isLessThan'),
                    '<=' => $this->GetLocalizerCaptions()->GetMessageString('isLessThanOrEqualsTo'),
                    '>' => $this->GetLocalizerCaptions()->GetMessageString('isGreaterThan'),
                    '>=' => $this->GetLocalizerCaptions()->GetMessageString('isGreaterThanOrEqualsTo'),
                    'ILIKE' => $this->GetLocalizerCaptions()->GetMessageString('Like'),
                    'STARTS' => $this->GetLocalizerCaptions()->GetMessageString('StartsWith'),
                    'ENDS' => $this->GetLocalizerCaptions()->GetMessageString('EndsWith'),
                    'CONTAINS' => $this->GetLocalizerCaptions()->GetMessageString('Contains')
                    ), $this->GetLocalizerCaptions(), $this, 'CONTAINS'
                );
        }
    
        protected function CreateGridAdvancedSearchControl(Grid $grid)
        {
            $this->AdvancedSearchControl = new AdvancedSearchControl('published_unique_ddiasearch', $this->dataset, $this->GetLocalizerCaptions(), $this->GetColumnVariableContainer(), $this->CreateLinkBuilder());
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('published_ddi_id', $this->RenderText('Published Ddi Id')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('drug1', $this->RenderText('Drug1')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('drug2', $this->RenderText('Drug2')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('event', $this->RenderText('Event')));
            
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`drug_vocabularies`');
            $field = new IntegerField('drug_vocab_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('vocab_name');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('vocab_uri_prefix');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $lookupDataset->setOrderByField('vocab_name', GetOrderTypeAsSQL(otAscending));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateLookupSearchInput('drug_vocabulary', $this->RenderText('Drug Vocabulary'), $lookupDataset, 'drug_vocab_id', 'vocab_name', false, 8));
            
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`event_vocabularies`');
            $field = new IntegerField('event_vocab_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('event_vocab_name');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('event_vocab_prefix');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $lookupDataset->setOrderByField('event_vocab_name', GetOrderTypeAsSQL(otAscending));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateLookupSearchInput('event_vocabulary', $this->RenderText('Event Vocabulary'), $lookupDataset, 'event_vocab_id', 'event_vocab_name', false, 8));
            
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`journals`');
            $field = new IntegerField('journal_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('journal_title');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('journal_website');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $lookupDataset->setOrderByField('journal_title', GetOrderTypeAsSQL(otAscending));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateLookupSearchInput('acceptance_venue', $this->RenderText('Acceptance Venue'), $lookupDataset, 'journal_id', 'journal_title', false, 8));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('paper_title', $this->RenderText('Paper Title')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('pubmed_id', $this->RenderText('Pubmed Id')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('DOI', $this->RenderText('DOI')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('unique_ddi_identifier', $this->RenderText('Unique Ddi Identifier')));
        }
    
        protected function AddOperationsColumns(Grid $grid)
        {
            $actions = $grid->getActions();
            $actions->setCaption($this->GetLocalizerCaptions()->GetMessageString('Actions'));
            $actions->setPosition(ActionList::POSITION_LEFT);
            if ($this->GetSecurityInfo()->HasViewGrant())
            {
                $operation = new LinkOperation($this->GetLocalizerCaptions()->GetMessageString('View'), OPERATION_VIEW, $this->dataset, $grid);
                $operation->setUseImage(true);
                $actions->addOperation($operation);
            }
            if ($this->GetSecurityInfo()->HasEditGrant())
            {
                $operation = new LinkOperation($this->GetLocalizerCaptions()->GetMessageString('Edit'), OPERATION_EDIT, $this->dataset, $grid);
                $operation->setUseImage(true);
                $actions->addOperation($operation);
                $operation->OnShow->AddListener('ShowEditButtonHandler', $this);
            }
            if ($this->GetSecurityInfo()->HasDeleteGrant())
            {
                $operation = new LinkOperation($this->GetLocalizerCaptions()->GetMessageString('Delete'), OPERATION_DELETE, $this->dataset, $grid);
                $operation->setUseImage(true);
                $actions->addOperation($operation);
                $operation->OnShow->AddListener('ShowDeleteButtonHandler', $this);
                $operation->SetAdditionalAttribute('data-modal-operation', 'delete');
                $operation->SetAdditionalAttribute('data-delete-handler-name', $this->GetModalGridDeleteHandler());
            }
            if ($this->GetSecurityInfo()->HasAddGrant())
            {
                $operation = new LinkOperation($this->GetLocalizerCaptions()->GetMessageString('Copy'), OPERATION_COPY, $this->dataset, $grid);
                $operation->setUseImage(true);
                $actions->addOperation($operation);
            }
        }
    
        protected function AddFieldColumns(Grid $grid, $withDetails = true)
        {
            //
            // View column for published_ddi_id field
            //
            $column = new TextViewColumn('published_ddi_id', 'Published Ddi Id', $this->dataset);
            $column->SetOrderable(true);
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for drug1 field
            //
            $column = new TextViewColumn('drug1', 'Drug1', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_drug1_handler_list');
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('1em');
            $grid->AddViewColumn($column);
            
            //
            // View column for drug2 field
            //
            $column = new TextViewColumn('drug2', 'Drug2', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_drug2_handler_list');
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for event field
            //
            $column = new TextViewColumn('event', 'Event', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_event_handler_list');
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for vocab_name field
            //
            $column = new TextViewColumn('drug_vocabulary_vocab_name', 'Drug Vocabulary', $this->dataset);
            $column->SetOrderable(true);
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for event_vocab_name field
            //
            $column = new TextViewColumn('event_vocabulary_event_vocab_name', 'Event Vocabulary', $this->dataset);
            $column->SetOrderable(true);
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for journal_title field
            //
            $column = new TextViewColumn('acceptance_venue_journal_title', 'Acceptance Venue', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_acceptance_venue_journal_title_handler_list');
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for paper_title field
            //
            $column = new TextViewColumn('paper_title', 'Paper Title', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_paper_title_handler_list');
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for pubmed_id field
            //
            $column = new TextViewColumn('pubmed_id', 'Pubmed Id', $this->dataset);
            $column->SetOrderable(true);
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for DOI field
            //
            $column = new TextViewColumn('DOI', 'DOI', $this->dataset);
            $column->SetOrderable(true);
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
            
            //
            // View column for unique_ddi_identifier field
            //
            $column = new TextViewColumn('unique_ddi_identifier', 'Unique Ddi Identifier', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_unique_ddi_identifier_handler_list');
            $column->setMinimalVisibility(ColumnVisibility::PHONE);
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth(null);
            $grid->AddViewColumn($column);
        }
    
        protected function AddSingleRecordViewColumns(Grid $grid)
        {
            //
            // View column for published_ddi_id field
            //
            $column = new TextViewColumn('published_ddi_id', 'Published Ddi Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for drug1 field
            //
            $column = new TextViewColumn('drug1', 'Drug1', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_drug1_handler_view');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for drug2 field
            //
            $column = new TextViewColumn('drug2', 'Drug2', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_drug2_handler_view');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for event field
            //
            $column = new TextViewColumn('event', 'Event', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_event_handler_view');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for vocab_name field
            //
            $column = new TextViewColumn('drug_vocabulary_vocab_name', 'Drug Vocabulary', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for event_vocab_name field
            //
            $column = new TextViewColumn('event_vocabulary_event_vocab_name', 'Event Vocabulary', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for journal_title field
            //
            $column = new TextViewColumn('acceptance_venue_journal_title', 'Acceptance Venue', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_acceptance_venue_journal_title_handler_view');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for paper_title field
            //
            $column = new TextViewColumn('paper_title', 'Paper Title', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_paper_title_handler_view');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for pubmed_id field
            //
            $column = new TextViewColumn('pubmed_id', 'Pubmed Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for DOI field
            //
            $column = new TextViewColumn('DOI', 'DOI', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for unique_ddi_identifier field
            //
            $column = new TextViewColumn('unique_ddi_identifier', 'Unique Ddi Identifier', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('published_unique_ddiGrid_unique_ddi_identifier_handler_view');
            $grid->AddSingleRecordViewColumn($column);
        }
    
        protected function AddEditColumns(Grid $grid)
        {
            //
            // Edit column for drug1 field
            //
            $editor = new TextEdit('drug1_edit');
            $editColumn = new CustomEditColumn('Drug1', 'drug1', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(150, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for drug2 field
            //
            $editor = new TextEdit('drug2_edit');
            $editColumn = new CustomEditColumn('Drug2', 'drug2', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(150, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for event field
            //
            $editor = new TextEdit('event_edit');
            $editColumn = new CustomEditColumn('Event', 'event', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(150, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for drug_vocabulary field
            //
            $editor = new ComboBox('drug_vocabulary_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`drug_vocabularies`');
            $field = new IntegerField('drug_vocab_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('vocab_name');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('vocab_uri_prefix');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $editColumn = new LookUpEditColumn(
                'Drug Vocabulary', 
                'drug_vocabulary', 
                $editor, 
                $this->dataset, 'drug_vocab_id', 'vocab_name', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for event_vocabulary field
            //
            $editor = new ComboBox('event_vocabulary_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`event_vocabularies`');
            $field = new IntegerField('event_vocab_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('event_vocab_name');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('event_vocab_prefix');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $editColumn = new LookUpEditColumn(
                'Event Vocabulary', 
                'event_vocabulary', 
                $editor, 
                $this->dataset, 'event_vocab_id', 'event_vocab_name', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for acceptance_venue field
            //
            $editor = new ComboBox('acceptance_venue_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`journals`');
            $field = new IntegerField('journal_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('journal_title');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('journal_website');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $editColumn = new LookUpEditColumn(
                'Acceptance Venue', 
                'acceptance_venue', 
                $editor, 
                $this->dataset, 'journal_id', 'journal_title', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for paper_title field
            //
            $editor = new TextAreaEdit('paper_title_edit', 250, 2);
            $editColumn = new CustomEditColumn('Paper Title', 'paper_title', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for pubmed_id field
            //
            $editor = new TextEdit('pubmed_id_edit');
            $editor->SetMaxLength(50);
            $editColumn = new CustomEditColumn('Pubmed Id', 'pubmed_id', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(50, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for DOI field
            //
            $editor = new TextEdit('doi_edit');
            $editor->SetMaxLength(50);
            $editColumn = new CustomEditColumn('DOI', 'DOI', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(50, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for unique_ddi_identifier field
            //
            $editor = new TextEdit('unique_ddi_identifier_edit');
            $editor->SetMaxLength(100);
            $editColumn = new CustomEditColumn('Unique Ddi Identifier', 'unique_ddi_identifier', $editor, $this->dataset);
            $editColumn->SetReadOnly(true);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
        }
    
        protected function AddInsertColumns(Grid $grid)
        {
            //
            // Edit column for drug1 field
            //
            $editor = new TextEdit('drug1_edit');
            $editColumn = new CustomEditColumn('Drug1', 'drug1', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(150, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for drug2 field
            //
            $editor = new TextEdit('drug2_edit');
            $editColumn = new CustomEditColumn('Drug2', 'drug2', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(150, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for event field
            //
            $editor = new TextEdit('event_edit');
            $editColumn = new CustomEditColumn('Event', 'event', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(150, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for drug_vocabulary field
            //
            $editor = new ComboBox('drug_vocabulary_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`drug_vocabularies`');
            $field = new IntegerField('drug_vocab_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('vocab_name');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('vocab_uri_prefix');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $editColumn = new LookUpEditColumn(
                'Drug Vocabulary', 
                'drug_vocabulary', 
                $editor, 
                $this->dataset, 'drug_vocab_id', 'vocab_name', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for event_vocabulary field
            //
            $editor = new ComboBox('event_vocabulary_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`event_vocabularies`');
            $field = new IntegerField('event_vocab_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('event_vocab_name');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('event_vocab_prefix');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $editColumn = new LookUpEditColumn(
                'Event Vocabulary', 
                'event_vocabulary', 
                $editor, 
                $this->dataset, 'event_vocab_id', 'event_vocab_name', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for acceptance_venue field
            //
            $editor = new ComboBox('acceptance_venue_edit', $this->GetLocalizerCaptions()->GetMessageString('PleaseSelect'));
            $lookupDataset = new TableDataset(
                new MyPDOConnectionFactory(),
                GetConnectionOptions(),
                '`journals`');
            $field = new IntegerField('journal_id', null, null, true);
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, true);
            $field = new StringField('journal_title');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $field = new StringField('journal_website');
            $field->SetIsNotNull(true);
            $lookupDataset->AddField($field, false);
            $editColumn = new LookUpEditColumn(
                'Acceptance Venue', 
                'acceptance_venue', 
                $editor, 
                $this->dataset, 'journal_id', 'journal_title', $lookupDataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for paper_title field
            //
            $editor = new TextAreaEdit('paper_title_edit', 250, 2);
            $editColumn = new CustomEditColumn('Paper Title', 'paper_title', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for pubmed_id field
            //
            $editor = new TextEdit('pubmed_id_edit');
            $editor->SetMaxLength(50);
            $editColumn = new CustomEditColumn('Pubmed Id', 'pubmed_id', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(50, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for DOI field
            //
            $editor = new TextEdit('doi_edit');
            $editor->SetMaxLength(50);
            $editColumn = new CustomEditColumn('DOI', 'DOI', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MaxLengthValidator(50, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MaxlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $validator = new MinLengthValidator(0, StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('MinlengthValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for unique_ddi_identifier field
            //
            $editor = new TextEdit('unique_ddi_identifier_edit');
            $editor->SetMaxLength(100);
            $editColumn = new CustomEditColumn('Unique Ddi Identifier', 'unique_ddi_identifier', $editor, $this->dataset);
            $editColumn->SetReadOnly(true);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            if ($this->GetSecurityInfo()->HasAddGrant())
            {
                $grid->SetShowAddButton(true);
                $grid->SetShowInlineAddButton(false);
            }
            else
            {
                $grid->SetShowInlineAddButton(false);
                $grid->SetShowAddButton(false);
            }
        }
    
        protected function AddPrintColumns(Grid $grid)
        {
            //
            // View column for published_ddi_id field
            //
            $column = new TextViewColumn('published_ddi_id', 'Published Ddi Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for drug1 field
            //
            $column = new TextViewColumn('drug1', 'Drug1', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for drug2 field
            //
            $column = new TextViewColumn('drug2', 'Drug2', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for event field
            //
            $column = new TextViewColumn('event', 'Event', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for vocab_name field
            //
            $column = new TextViewColumn('drug_vocabulary_vocab_name', 'Drug Vocabulary', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for event_vocab_name field
            //
            $column = new TextViewColumn('event_vocabulary_event_vocab_name', 'Event Vocabulary', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for acceptance_venue field
            //
            $column = new TextViewColumn('acceptance_venue', 'Acceptance Venue', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for paper_title field
            //
            $column = new TextViewColumn('paper_title', 'Paper Title', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for pubmed_id field
            //
            $column = new TextViewColumn('pubmed_id', 'Pubmed Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for DOI field
            //
            $column = new TextViewColumn('DOI', 'DOI', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
            
            //
            // View column for unique_ddi_identifier field
            //
            $column = new TextViewColumn('unique_ddi_identifier', 'Unique Ddi Identifier', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddPrintColumn($column);
        }
    
        protected function AddExportColumns(Grid $grid)
        {
            //
            // View column for published_ddi_id field
            //
            $column = new TextViewColumn('published_ddi_id', 'Published Ddi Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for drug1 field
            //
            $column = new TextViewColumn('drug1', 'Drug1', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for drug2 field
            //
            $column = new TextViewColumn('drug2', 'Drug2', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for event field
            //
            $column = new TextViewColumn('event', 'Event', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for vocab_name field
            //
            $column = new TextViewColumn('drug_vocabulary_vocab_name', 'Drug Vocabulary', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for event_vocab_name field
            //
            $column = new TextViewColumn('event_vocabulary_event_vocab_name', 'Event Vocabulary', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for acceptance_venue field
            //
            $column = new TextViewColumn('acceptance_venue', 'Acceptance Venue', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for paper_title field
            //
            $column = new TextViewColumn('paper_title', 'Paper Title', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for pubmed_id field
            //
            $column = new TextViewColumn('pubmed_id', 'Pubmed Id', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for DOI field
            //
            $column = new TextViewColumn('DOI', 'DOI', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
            
            //
            // View column for unique_ddi_identifier field
            //
            $column = new TextViewColumn('unique_ddi_identifier', 'Unique Ddi Identifier', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddExportColumn($column);
        }
    
        public function GetPageDirection()
        {
            return null;
        }
    
        protected function ApplyCommonColumnEditProperties(CustomEditColumn $column)
        {
            $column->SetDisplaySetToNullCheckBox(false);
            $column->SetDisplaySetToDefaultCheckBox(false);
    		$column->SetVariableContainer($this->GetColumnVariableContainer());
        }
    
        function GetCustomClientScript()
        {
            return ;
        }
        
        function GetOnPageLoadedClientScript()
        {
            return ;
        }
        public function ShowEditButtonHandler(&$show)
        {
            if ($this->GetRecordPermission() != null)
                $show = $this->GetRecordPermission()->HasEditGrant($this->GetDataset());
        }
        
        public function ShowDeleteButtonHandler(&$show)
        {
            if ($this->GetRecordPermission() != null)
                $show = $this->GetRecordPermission()->HasDeleteGrant($this->GetDataset());
        }
        
        public function GetModalGridDeleteHandler() { return 'published_unique_ddi_modal_delete'; }
        protected function GetEnableModalGridDelete() { return true; }
    
        protected function CreateGrid()
        {
            $result = new Grid($this, $this->dataset, 'published_unique_ddiGrid');
            if ($this->GetSecurityInfo()->HasDeleteGrant())
               $result->SetAllowDeleteSelected(true);
            else
               $result->SetAllowDeleteSelected(false);   
            
            ApplyCommonPageSettings($this, $result);
            
            $result->SetUseImagesForActions(true);
            $result->SetInsertClientEditorValueChangedScript($this->RenderText('    if ((sender.getFieldName() == \'drug1\') || (sender.getFieldName() == \'drug2\') || (sender.getFieldName() == \'event\')) { 
                    var hash = \'\';
                    var drug1 = editors[\'drug1\'].getValue();
                    var drug2 = editors[\'drug2\'].getValue();
                    var event = editors[\'event\'].getValue();
                    var toHash = (drug1.concat(drug2)).concat(event); 
            
                    var a = toHash.split(""),
                        n = a.length;
            
                    for(var i = n - 1; i > 0; i--) {
                           var j = Math.floor(Math.random() * (i + 1));
                           var tmp = a[i];
                           a[i] = a[j];
                           a[j] = tmp;
                    }
                    var to_page = a.join("");
            
            
                    editors[\'unique_ddi_identifier\'].setValue(to_page);    
                }'));
            
            $result->SetEditClientEditorValueChangedScript($this->RenderText('    if ((sender.getFieldName() == \'drug1\') || (sender.getFieldName() == \'drug2\') || (sender.getFieldName() == \'event\')) { 
                    var hash = \'\';
                    var drug1 = editors[\'drug1\'].getValue();
                    var drug2 = editors[\'drug2\'].getValue();
                    var event = editors[\'event\'].getValue();
                    var toHash = (drug1.concat(drug2)).concat(event); 
            
                    var a = toHash.split(""),
                        n = a.length;
            
                    for(var i = n - 1; i > 0; i--) {
                           var j = Math.floor(Math.random() * (i + 1));
                           var tmp = a[i];
                           a[i] = a[j];
                           a[j] = tmp;
                    }
                    var to_page = a.join("");
            
            
                    editors[\'unique_ddi_identifier\'].setValue(to_page);    
                }'));
            $result->SetUseFixedHeader(false);
            $result->SetShowLineNumbers(false);
            $result->SetShowKeyColumnsImagesInHeader(false);
            $result->SetViewMode(ViewMode::TABLE);
            $result->setEnableRuntimeCustomization(true);
            $result->setTableBordered(true);
            $result->setTableCondensed(false);
            
            $result->SetHighlightRowAtHover(false);
            $result->SetWidth('');
            $this->CreateGridSearchControl($result);
            $this->CreateGridAdvancedSearchControl($result);
            $this->AddOperationsColumns($result);
            $this->AddFieldColumns($result);
            $this->AddSingleRecordViewColumns($result);
            $this->AddEditColumns($result);
            $this->AddInsertColumns($result);
            $this->AddPrintColumns($result);
            $this->AddExportColumns($result);
    
            $this->SetShowPageList(true);
            $this->SetSimpleSearchAvailable(true);
            $this->SetAdvancedSearchAvailable(true);
            $this->SetShowTopPageNavigator(true);
            $this->SetShowBottomPageNavigator(true);
            $this->setPrintListAvailable(true);
            $this->setPrintListRecordAvailable(false);
            $this->setPrintOneRecordAvailable(true);
            $this->setExportListAvailable(array('excel','word','xml','csv','pdf'));
            $this->setExportListRecordAvailable(array());
            $this->setExportOneRecordAvailable(array('excel','word','xml','csv','pdf'));
    
            //
            // Http Handlers
            //
            //
            // View column for drug1 field
            //
            $column = new TextViewColumn('drug1', 'Drug1', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_drug1_handler_list', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for drug2 field
            //
            $column = new TextViewColumn('drug2', 'Drug2', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_drug2_handler_list', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for event field
            //
            $column = new TextViewColumn('event', 'Event', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_event_handler_list', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for journal_title field
            //
            $column = new TextViewColumn('acceptance_venue_journal_title', 'Acceptance Venue', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_acceptance_venue_journal_title_handler_list', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for paper_title field
            //
            $column = new TextViewColumn('paper_title', 'Paper Title', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_paper_title_handler_list', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for unique_ddi_identifier field
            //
            $column = new TextViewColumn('unique_ddi_identifier', 'Unique Ddi Identifier', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_unique_ddi_identifier_handler_list', $column);
            GetApplication()->RegisterHTTPHandler($handler);//
            // View column for drug1 field
            //
            $column = new TextViewColumn('drug1', 'Drug1', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_drug1_handler_view', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for drug2 field
            //
            $column = new TextViewColumn('drug2', 'Drug2', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_drug2_handler_view', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for event field
            //
            $column = new TextViewColumn('event', 'Event', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_event_handler_view', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for journal_title field
            //
            $column = new TextViewColumn('acceptance_venue_journal_title', 'Acceptance Venue', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_acceptance_venue_journal_title_handler_view', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for paper_title field
            //
            $column = new TextViewColumn('paper_title', 'Paper Title', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_paper_title_handler_view', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for unique_ddi_identifier field
            //
            $column = new TextViewColumn('unique_ddi_identifier', 'Unique Ddi Identifier', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'published_unique_ddiGrid_unique_ddi_identifier_handler_view', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            return $result;
        }
        
        public function OpenAdvancedSearchByDefault()
        {
            return false;
        }
    
        protected function DoGetGridHeader()
        {
            return '';
        }
    }

    SetUpUserAuthorization(GetApplication());

    try
    {
        $Page = new published_unique_ddiPage("published_unique_ddi.php", "published_unique_ddi", GetCurrentUserGrantForDataSource("published_unique_ddi"), 'UTF-8');
        $Page->SetTitle('Published Single Drug-Drug Interaction Submission');
        $Page->SetMenuLabel('Register published single drug-drug interaction');
        $Page->SetHeader(GetPagesHeader());
        $Page->SetFooter(GetPagesFooter());
        $Page->SetRecordPermission(GetCurrentUserRecordPermissionsForDataSource("published_unique_ddi"));
        GetApplication()->SetEnableLessRunTimeCompile(GetEnableLessFilesRunTimeCompilation());
        GetApplication()->SetCanUserChangeOwnPassword(
            !function_exists('CanUserChangeOwnPassword') || CanUserChangeOwnPassword());
        GetApplication()->SetMainPage($Page);
        GetApplication()->Run();
    }
    catch(Exception $e)
    {
        ShowErrorPage($e->getMessage());
    }
	
