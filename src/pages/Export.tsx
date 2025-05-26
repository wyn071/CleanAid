import React, { useState, useEffect } from 'react';
import {
  Card,
  Form,
  Button,
  Table,
  Badge,
  Alert,
  Row,
  Col,
  ProgressBar,
  Tooltip,
  OverlayTrigger,
  Modal
} from 'react-bootstrap';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import * as XLSX from 'xlsx';
import { BeneficiaryRecord, DataIssue } from '../services/DataProcessingService';

interface ExportProps {
  flaggedRecords: BeneficiaryRecord[];
  issues: DataIssue[];
}

interface ExportHistoryItem {
  id: string;
  dataset: string;
  format: string;
  date: string;
  status: 'completed' | 'failed' | 'processing';
  downloadUrl?: string;
  recordCount: number;
  fileSize?: string;
}

interface ExportTemplate {
  id: string;
  name: string;
  fields: string[];
  description: string;
}

const Export: React.FC<ExportProps> = ({ flaggedRecords, issues }) => {
  const [startDate, setStartDate] = useState<Date | null>(null);
  const [endDate, setEndDate] = useState<Date | null>(null);
  const [exportFormat, setExportFormat] = useState<'xlsx' | 'csv'>('xlsx');
  const [includeHeaders, setIncludeHeaders] = useState(true);
  const [includeMetadata, setIncludeMetadata] = useState(true);
  const [isExporting, setIsExporting] = useState(false);
  const [progress, setProgress] = useState(0);
  const [message, setMessage] = useState<{ type: 'success' | 'danger' | 'info', text: string } | null>(null);
  const [exportHistory, setExportHistory] = useState<ExportHistoryItem[]>([]);
  const [selectedFields, setSelectedFields] = useState<string[]>([]);
  const [showTemplateModal, setShowTemplateModal] = useState(false);

  const templates: ExportTemplate[] = [
    {
      id: '1',
      name: 'Basic Export',
      fields: ['id', 'name', 'program', 'amount', 'status'],
      description: 'Basic fields for quick export'
    },
    {
      id: '2',
      name: 'Detailed Export',
      fields: ['id', 'name', 'program', 'amount', 'status', 'address', 'contact', 'issues'],
      description: 'All fields including detailed information'
    },
    {
      id: '3',
      name: 'Issues Only',
      fields: ['id', 'name', 'issues'],
      description: 'Focus on records with issues'
    }
  ];

  useEffect(() => {
    const saved = localStorage.getItem('exportHistory');
    if (saved) setExportHistory(JSON.parse(saved));
  }, []);

  useEffect(() => {
    localStorage.setItem('exportHistory', JSON.stringify(exportHistory));
  }, [exportHistory]);

  const handleExport = async () => {
    setIsExporting(true);
    setProgress(0);
    setMessage(null);

    try {
      let recordsToExport = flaggedRecords;
      if (startDate && endDate) {
        recordsToExport = recordsToExport.filter(record => {
          const recordDate = new Date(record.date);
          return recordDate >= startDate && recordDate <= endDate;
        });
      }

      const processingExport: ExportHistoryItem = {
        id: Date.now().toString(),
        dataset: 'Flagged Records',
        format: exportFormat.toUpperCase(),
        date: new Date().toLocaleString(),
        status: 'processing',
        recordCount: recordsToExport.length
      };
      setExportHistory(prev => [processingExport, ...prev]);

      const interval = setInterval(() => {
        setProgress(prev => {
          if (prev >= 90) {
            clearInterval(interval);
            return prev;
          }
          return prev + 10;
        });
      }, 200);

      const exportData = recordsToExport.map(record => {
        const recordIssues = issues.filter(issue => issue.recordId === record.id);
        const baseRecord = {
          ...record,
          issues: recordIssues.map(issue => `${issue.type} (${issue.severity}): ${issue.description}`).join('; ')
        };

        if (selectedFields.length > 0) {
          return Object.fromEntries(
            selectedFields.map(field => [field, (baseRecord as Record<string, any>)[field]])
          );
        }

        return baseRecord;
      });

      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.json_to_sheet(exportData);

      if (includeMetadata) {
        const metadata = [
          ['Export Date', new Date().toLocaleString()],
          ['Total Records', exportData.length],
          ['Format', exportFormat.toUpperCase()],
          ['Date Range', `${startDate?.toLocaleDateString() || 'All'} to ${endDate?.toLocaleDateString() || 'All'}`],
          ['Selected Fields', selectedFields.length > 0 ? selectedFields.join(', ') : 'All']
        ];
        XLSX.utils.sheet_add_aoa(ws, metadata, { origin: -1 });
      }

      XLSX.utils.book_append_sheet(wb, ws, 'Flagged Records');

      const blob = new Blob([XLSX.write(wb, { type: 'array', bookType: exportFormat })]);
      const downloadUrl = URL.createObjectURL(blob);
      const fileSize = `${(blob.size / 1024).toFixed(2)} KB`;

      const completedExport: ExportHistoryItem = {
        ...processingExport,
        status: 'completed',
        downloadUrl,
        fileSize
      };

      setExportHistory(prev => prev.map(e => e.id === processingExport.id ? completedExport : e));
      setProgress(100);
      setMessage({ type: 'success', text: 'Data exported successfully!' });
    } catch (error) {
      console.error(error);
      setMessage({ type: 'danger', text: 'Failed to export data. Please try again.' });
      setExportHistory(prev =>
        prev.map(item => item.status === 'processing' ? { ...item, status: 'failed' } : item)
      );
    } finally {
      setIsExporting(false);
    }
  };

  const renderTooltip = (props: any) => (
    <Tooltip id="template-tooltip" {...props}>
      Select an export template
    </Tooltip>
  );

  return (
    <div className="container-fluid py-4">
      <Card className="data-card border-0 shadow-sm">
        <Card.Body>
          <div className="section-header mb-4 d-flex justify-content-between align-items-center">
            <div>
              <h4 className="mb-1">Export Flagged Records</h4>
              <p className="text-muted mb-0">Export filtered beneficiary records</p>
            </div>
            <OverlayTrigger overlay={renderTooltip} placement="left">
              <Button variant="outline-danger" size="sm" onClick={() => setShowTemplateModal(true)}>
                <i className="bi bi-file-earmark-text me-2"></i>
                Export Templates
              </Button>
            </OverlayTrigger>
          </div>

          <Form>
            <Row className="g-3 mb-3">
              <Col md={6}>
                <Form.Group>
                  <Form.Label>Date Range</Form.Label>
                  <div className="d-flex gap-2">
                    <DatePicker selected={startDate} onChange={setStartDate} className="form-control" placeholderText="Start" />
                    <DatePicker selected={endDate} onChange={setEndDate} className="form-control" placeholderText="End" />
                  </div>
                </Form.Group>
              </Col>
              <Col md={6}>
                <Form.Group>
                  <Form.Label>Export Format</Form.Label>
                  <Form.Select value={exportFormat} onChange={e => setExportFormat(e.target.value as 'xlsx' | 'csv')}>
                    <option value="xlsx">Excel (XLSX)</option>
                    <option value="csv">CSV</option>
                  </Form.Select>
                </Form.Group>
              </Col>
            </Row>

            <Row className="g-3 mb-3">
              <Col md={4}>
                <Form.Check type="checkbox" label="Include Headers" checked={includeHeaders} onChange={e => setIncludeHeaders(e.target.checked)} />
              </Col>
              <Col md={4}>
                <Form.Check type="checkbox" label="Include Metadata" checked={includeMetadata} onChange={e => setIncludeMetadata(e.target.checked)} />
              </Col>
              <Col md={4}>
                <Form.Check type="checkbox" label="Selected Fields Only" checked={selectedFields.length > 0} onChange={e => !e.target.checked && setSelectedFields([])} />
              </Col>
            </Row>

            <Button variant="danger" onClick={handleExport} disabled={isExporting}>
              {isExporting ? 'Exporting...' : 'Export Records'}
            </Button>
          </Form>

          {isExporting && (
            <div className="mt-4">
              <ProgressBar animated now={progress} label={`${progress}%`} variant="danger" />
            </div>
          )}

          {message && <Alert variant={message.type} className="mt-3">{message.text}</Alert>}

          {exportHistory.length > 0 && (
            <div className="mt-5">
              <h5>Export History</h5>
              <Table className="data-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Format</th>
                    <th>Records</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  {exportHistory.map(item => (
                    <tr key={item.id}>
                      <td>{item.date}</td>
                      <td>{item.format}</td>
                      <td>{item.recordCount}</td>
                      <td>{item.fileSize || '-'}</td>
                      <td>
                        <Badge bg={item.status === 'completed' ? 'success' : item.status === 'processing' ? 'warning' : 'danger'}>
                          {item.status}
                        </Badge>
                      </td>
                      <td>
                        {item.downloadUrl && item.status === 'completed' && (
                          <a href={item.downloadUrl} download className="btn btn-outline-primary btn-sm">
                            <i className="bi bi-download me-1"></i> Download
                          </a>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </Table>
            </div>
          )}
        </Card.Body>
      </Card>

      <Modal show={showTemplateModal} onHide={() => setShowTemplateModal(false)}>
        <Modal.Header closeButton>
          <Modal.Title>Select Export Template</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {templates.map(template => (
            <Card key={template.id} className="mb-3">
              <Card.Body>
                <div className="d-flex justify-content-between align-items-start">
                  <div>
                    <h6>{template.name}</h6>
                    <p className="text-muted small">{template.description}</p>
                    {template.fields.map(f => (
                      <Badge key={f} bg="light" text="dark" className="me-1">{f}</Badge>
                    ))}
                  </div>
                  <Button size="sm" variant="outline-danger" onClick={() => {
                    setSelectedFields(template.fields);
                    setShowTemplateModal(false);
                  }}>
                    Use Template
                  </Button>
                </div>
              </Card.Body>
            </Card>
          ))}
        </Modal.Body>
      </Modal>
    </div>
  );
};

export default Export;
