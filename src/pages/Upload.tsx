import React, { useState, useCallback, useEffect } from 'react';
import { Card, Button, ProgressBar, Alert, Row, Col, Badge, Tooltip, OverlayTrigger } from 'react-bootstrap';
import { useDropzone } from 'react-dropzone';
import DataProcessingService, { ProcessingResult, BeneficiaryRecord } from '../services/DataProcessingService';
import Review from './Review';
import Export from './Export';
import '../styles/shared.css';

const Upload: React.FC = () => {
  const [file, setFile] = useState<File | null>(null);
  const [processing, setProcessing] = useState(false);
  const [progress, setProgress] = useState(0);
  const [result, setResult] = useState<ProcessingResult | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [showReview, setShowReview] = useState(false);
  const [showSuccess, setShowSuccess] = useState(false);
  const [uploadHistory, setUploadHistory] = useState<Array<{name: string, date: string, status: string}>>([]);
  const [validationRules, setValidationRules] = useState({
    checkDuplicates: true,
    validateFormat: true,
    checkRequired: true,
    validateDates: true
  });

  useEffect(() => {
    // Load recent upload history
    const history = [
      { name: 'Ayuda_List_2024.xlsx', date: '2024-03-15', status: 'success' },
      { name: 'Beneficiaries_March.csv', date: '2024-03-14', status: 'warning' },
      { name: 'DSWD_Data.xlsx', date: '2024-03-13', status: 'success' }
    ];
    setUploadHistory(history);
  }, []);

  const onDrop = useCallback((acceptedFiles: File[]) => {
    const uploadedFile = acceptedFiles[0];
    if (uploadedFile) {
      setFile(uploadedFile);
      setError(null);
      setResult(null);
      setShowReview(false);
      setShowSuccess(false);
    }
  }, []);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: {
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx'],
      'application/vnd.ms-excel': ['.xls'],
      'text/csv': ['.csv']
    },
    multiple: false,
  });

  const handleProcess = async () => {
    if (!file) {
      setError('Please select a file to upload.');
      return;
    }

    try {
      setProcessing(true);
      setProgress(0);
      setError(null);
      setResult(null);
      setShowReview(false);
      setShowSuccess(false);

      const progressInterval = setInterval(() => {
        setProgress(prev => {
          if (prev >= 90) {
            clearInterval(progressInterval);
            return prev;
          }
          return prev + 10;
        });
      }, 200);

      const initialResult = await DataProcessingService.scanDataset(file);
      if (!initialResult || !Array.isArray(initialResult.records)) {
        throw new Error('Invalid data format received from processing service');
      }

      const categorizedIssues = await DataProcessingService.categorizeIssues(initialResult.issues);
      const finalResult = { ...initialResult, issues: categorizedIssues };
      
      setResult(finalResult);
      setProgress(100);
      clearInterval(progressInterval);
      setShowSuccess(true);

      // Update upload history
      setUploadHistory(prev => [{
        name: file.name,
        date: new Date().toISOString().split('T')[0],
        status: categorizedIssues.length > 0 ? 'warning' : 'success'
      }, ...prev]);

      // Auto-hide success message after 5 seconds
      setTimeout(() => {
        setShowSuccess(false);
        setShowReview(true);
      }, 5000);

    } catch (err) {
      console.error('Error processing file:', err);
      setError(err instanceof Error ? err.message : 'An error occurred while processing the file');
      setProgress(0);
      setResult(null);
      setShowReview(false);
    } finally {
      setProcessing(false);
    }
  };

  const handleSaveReview = (updatedRecords: BeneficiaryRecord[]) => {
    if (result) {
      const updatedResult = { ...result, records: updatedRecords };
      setResult(updatedResult);
      console.log('Saving reviewed records:', updatedRecords);
    }
  };

  const handleReviewClick = () => {
    if (!result || !Array.isArray(result.records) || result.records.length === 0) {
      setError('No data available for review. Please process a file first.');
      setShowReview(false);
      return;
    }
    setShowReview(true);
  };

  const getFlaggedRecords = () => {
    if (!result || !Array.isArray(result.records) || !Array.isArray(result.issues)) return [];
    const recordsArray = Array.isArray(result.records) ? result.records : [];
    const issuesArray = Array.isArray(result.issues) ? result.issues : [];

    return recordsArray.filter(record =>
      issuesArray.some(issue => issue.recordId === record.id)
    );
  };

  const renderTooltip = (props: any) => (
    <Tooltip id="button-tooltip" {...props}>
      Click to toggle validation rule
    </Tooltip>
  );

  return (
    <div className="container-fluid py-4">
      {!showReview ? (
        <Card className="data-card border-0 shadow-sm">
          <Card.Body className="p-4">
            <div className="section-header mb-4">
              <div className="d-flex align-items-center mb-2">
                <img 
                  src="/dswd-logo.png" 
                  alt="DSWD Logo" 
                  style={{ width: '40px', height: '40px', marginRight: '15px' }} 
                />
                <h4 className="mb-0 text-danger">Data Upload</h4>
              </div>
              <p className="text-muted mb-0">Upload beneficiary data files for processing and validation</p>
            </div>

            {/* Success Message */}
            {showSuccess && (
              <Alert variant="success" className="mb-4 d-flex align-items-center">
                <i className="bi bi-check-circle-fill me-2 fs-4"></i>
                <div>
                  <h5 className="mb-1">File Processed Successfully!</h5>
                  <p className="mb-0">Your file has been processed and is ready for review.</p>
                </div>
              </Alert>
            )}

            <Row className="g-4 mb-4">
              <Col md={8}>
                {/* Dropzone Area */}
                <div
                  {...getRootProps()}
                  className={`dropzone p-5 mb-4 text-center border-2 border-dashed rounded-3 ${isDragActive ? 'active bg-light' : 'bg-white'}`}
                  style={{ 
                    borderColor: isDragActive ? '#dc3545' : '#dee2e6',
                    transition: 'all 0.3s ease'
                  }}
                >
                  <input {...getInputProps()} />
                  <div className="d-flex flex-column align-items-center justify-content-center">
                    <i className="bi bi-cloud-upload display-4 text-danger mb-3"></i>
                    {file ? (
                      <div className="text-center">
                        <p className="mb-2 fs-5">Selected file: {file.name}</p>
                        <p className="text-muted small">
                          {(file.size / 1024 / 1024).toFixed(2)} MB
                        </p>
                      </div>
                    ) : (
                      <div className="text-center">
                        <h5 className="mb-2">Drag & Drop your file here</h5>
                        <p className="text-muted mb-2">or click to select a file</p>
                        <p className="text-muted small">
                          Supported formats: CSV, XLSX, XLS
                        </p>
                      </div>
                    )}
                  </div>
                </div>

                {/* Upload Button */}
                {file && !processing && !result && !error && (
                  <div className="text-center">
                    <Button
                      variant="danger"
                      size="lg"
                      className="px-5"
                      onClick={handleProcess}
                      disabled={processing}
                    >
                      <i className="bi bi-upload me-2"></i>
                      Upload and Process File
                    </Button>
                  </div>
                )}

                {/* Processing Indicator */}
                {processing && (
                  <div className="mt-4">
                    <div className="d-flex justify-content-between mb-2">
                      <span className="text-danger">Processing file...</span>
                      <span className="text-danger">{progress}%</span>
                    </div>
                    <ProgressBar 
                      now={progress} 
                      variant="danger"
                      className="mb-4"
                    />
                  </div>
                )}

                {/* Error Message */}
                {error && (
                  <Alert variant="danger" className="mt-4 d-flex align-items-center">
                    <i className="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                    <div>
                      <h5 className="mb-1">Error Processing File</h5>
                      <p className="mb-0">{error}</p>
                    </div>
                  </Alert>
                )}

                {/* Processing Results Summary */}
                {!processing && result && !error && (
                  <div className="mt-4">
                    <div className="section-header mb-4">
                      <h4 className="text-danger">Processing Summary</h4>
                    </div>
                    <Row className="g-3">
                      <Col md={3}>
                        <Card className="stats-card border-0 shadow-sm">
                          <Card.Body>
                            <h6 className="text-muted">Total Records</h6>
                            <h3 className="text-danger mb-0">{result.statistics.totalRecords}</h3>
                          </Card.Body>
                        </Card>
                      </Col>
                      <Col md={3}>
                        <Card className="stats-card border-0 shadow-sm">
                          <Card.Body>
                            <h6 className="text-muted">Duplicates</h6>
                            <h3 className="text-warning mb-0">{result.statistics.duplicatesFound}</h3>
                          </Card.Body>
                        </Card>
                      </Col>
                      <Col md={3}>
                        <Card className="stats-card border-0 shadow-sm">
                          <Card.Body>
                            <h6 className="text-muted">Missing Data</h6>
                            <h3 className="text-danger mb-0">{result.statistics.missingData}</h3>
                          </Card.Body>
                        </Card>
                      </Col>
                      <Col md={3}>
                        <Card className="stats-card border-0 shadow-sm">
                          <Card.Body>
                            <h6 className="text-muted">Mismatches</h6>
                            <h3 className="text-info mb-0">{result.statistics.mismatches}</h3>
                          </Card.Body>
                        </Card>
                      </Col>
                    </Row>

                    {/* Review Button */}
                    {result && result.records.length > 0 && (
                      <div className="mt-4 text-center">
                        <Button
                          variant="danger"
                          size="lg"
                          className="px-5"
                          onClick={() => setShowReview(true)}
                        >
                          <i className="bi bi-eye me-2"></i>
                          Review Flagged Records
                          {getFlaggedRecords().length > 0 && (
                            <Badge bg="light" text="dark" className="ms-2">
                              {getFlaggedRecords().length}
                            </Badge>
                          )}
                        </Button>
                      </div>
                    )}

                    {/* No Flagged Records Message */}
                    {result && getFlaggedRecords().length === 0 && result.records.length > 0 && (
                      <Alert variant="success" className="mt-4 d-flex align-items-center">
                        <i className="bi bi-check-circle-fill me-2 fs-4"></i>
                        <div>
                          <h5 className="mb-1">No Issues Found</h5>
                          <p className="mb-0">All records in the dataset have been validated successfully.</p>
                        </div>
                      </Alert>
                    )}
                  </div>
                )}
              </Col>

              <Col md={4}>
                {/* Validation Rules */}
                <Card className="border-0 shadow-sm mb-4">
                  <Card.Body>
                    <h5 className="mb-3">Validation Rules</h5>
                    <div className="d-grid gap-2">
                      {Object.entries(validationRules).map(([rule, enabled]) => (
                        <OverlayTrigger
                          key={rule}
                          placement="left"
                          overlay={renderTooltip}
                        >
                          <Button
                            variant={enabled ? "danger" : "outline-danger"}
                            className="text-start"
                            onClick={() => setValidationRules(prev => ({
                              ...prev,
                              [rule]: !enabled
                            }))}
                          >
                            <i className={`bi ${enabled ? 'bi-check-circle-fill' : 'bi-circle'} me-2`}></i>
                            {rule.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase())}
                          </Button>
                        </OverlayTrigger>
                      ))}
                    </div>
                  </Card.Body>
                </Card>

                {/* Recent Uploads */}
                <Card className="border-0 shadow-sm">
                  <Card.Body>
                    <h5 className="mb-3">Recent Uploads</h5>
                    <div className="recent-uploads">
                      {uploadHistory.map((upload, index) => (
                        <div key={index} className="d-flex align-items-center mb-3">
                          <div className="flex-grow-1">
                            <p className="mb-0">{upload.name}</p>
                            <small className="text-muted">{upload.date}</small>
                          </div>
                          <Badge bg={
                            upload.status === 'success' ? 'success' :
                            upload.status === 'warning' ? 'warning' :
                            'danger'
                          }>
                            {upload.status}
                          </Badge>
                        </div>
                      ))}
                    </div>
                  </Card.Body>
                </Card>
              </Col>
            </Row>
          </Card.Body>
        </Card>
      ) : (
        <div>
          <Button
            variant="outline-danger"
            className="mb-4"
            onClick={() => { setShowReview(false); setResult(null); setFile(null); }}
          >
            <i className="bi bi-arrow-left me-2"></i>
            Back to Upload
          </Button>
          {result && (
            <>
              <Review result={result} onSave={handleSaveReview} />
              <div className="mt-4">
                <Export
                  flaggedRecords={getFlaggedRecords()}
                  issues={result.issues}
                />
              </div>
            </>
          )}
        </div>
      )}
    </div>
  );
};

export default Upload; 