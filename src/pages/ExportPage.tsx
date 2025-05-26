import React, { useState, useEffect } from 'react';
import {
  Card, Form, Button, Table, Alert, Row, Col, Badge
} from 'react-bootstrap';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import '../styles/shared.css';

interface DatasetOption {
  id: string;
  name: string;
}

interface ExportHistoryItem {
  id: string;
  datasetName: string;
  format: string;
  date: string;
  status: 'completed' | 'failed';
  downloadUrl?: string;
}

const ExportPage: React.FC = () => {
  const [selectedDataset, setSelectedDataset] = useState<string>('');
  const [startDate, setStartDate] = useState<Date | null>(null);
  const [endDate, setEndDate] = useState<Date | null>(null);
  const [exportFormat, setExportFormat] = useState<'csv' | 'xlsx'>('csv');
  const [isExporting, setIsExporting] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'danger' | 'info'; text: string } | null>(null);
  const [exportHistory, setExportHistory] = useState<ExportHistoryItem[]>([]);
  const [datasetOptions, setDatasetOptions] = useState<DatasetOption[]>([
    { id: '', name: '-- Choose dataset --' },
    { id: 'dataset1', name: 'Ayuda_May2025.csv (Processed 2023-10-26)' },
    { id: 'dataset2', name: 'Ayuda_June2025.csv (Processed 2023-11-15)' }
  ]);

  const handleExport = async () => {
    if (!selectedDataset) {
      setMessage({ type: 'danger', text: 'Please select a dataset to export.' });
      return;
    }

    setIsExporting(true);
    setMessage(null);

    try {
      // Simulated export delay
      await new Promise(resolve => setTimeout(resolve, 2000));

      const newExport: ExportHistoryItem = {
        id: Date.now().toString(),
        datasetName: datasetOptions.find(d => d.id === selectedDataset)?.name || selectedDataset,
        format: exportFormat.toUpperCase(),
        date: new Date().toLocaleString(),
        status: 'completed',
        downloadUrl: '/downloads/example-file.csv' // Placeholder
      };

      setExportHistory(prev => [newExport, ...prev]);
      setMessage({
        type: 'success',
        text: 'Export completed. You can download the file from the history table below.'
      });
    } catch (error) {
      console.error('Export error:', error);
      setMessage({ type: 'danger', text: 'Failed to export dataset. Please try again.' });
    } finally {
      setIsExporting(false);
    }
  };

  useEffect(() => {
    // TODO: Replace with actual API call
    // fetch('/api/datasets').then(...)
  }, []);

  return (
    <div className="container-fluid py-4">
      <Card className="data-card shadow-sm">
        <Card.Body>
          <div className="section-header mb-4">
            <h4>Export Cleansed Data</h4>
            <p className="text-muted">Download cleansed or flagged beneficiary datasets in Excel or CSV format.</p>
          </div>

          <Form>
            <Row className="g-3 mb-4">
              <Col md={6}>
                <Form.Group controlId="datasetSelect">
                  <Form.Label>Select Dataset</Form.Label>
                  <Form.Select value={selectedDataset} onChange={e => setSelectedDataset(e.target.value)}>
                    {datasetOptions.map(option => (
                      <option key={option.id} value={option.id}>{option.name}</option>
                    ))}
                  </Form.Select>
                </Form.Group>
              </Col>

              <Col md={6}>
                <Form.Group>
                  <Form.Label>Date Range (Optional)</Form.Label>
                  <div className="d-flex gap-2">
                    <DatePicker
                      selected={startDate}
                      onChange={setStartDate}
                      className="form-control"
                      placeholderText="Start Date"
                      dateFormat="yyyy-MM-dd"
                    />
                    <DatePicker
                      selected={endDate}
                      onChange={setEndDate}
                      className="form-control"
                      placeholderText="End Date"
                      dateFormat="yyyy-MM-dd"
                    />
                  </div>
                </Form.Group>
              </Col>
            </Row>

            <Row className="g-3 mb-4">
              <Col md={6}>
                <Form.Group>
                  <Form.Label>Export Format</Form.Label>
                  <div>
                    <Form.Check
                      inline label="CSV (.csv)" type="radio" id="formatCSV"
                      value="csv" checked={exportFormat === 'csv'}
                      onChange={() => setExportFormat('csv')}
                    />
                    <Form.Check
                      inline label="Excel (.xlsx)" type="radio" id="formatXLSX"
                      value="xlsx" checked={exportFormat === 'xlsx'}
                      onChange={() => setExportFormat('xlsx')}
                    />
                  </div>
                </Form.Group>
              </Col>
            </Row>

            {message && (
              <Alert variant={message.type} className="mt-2">{message.text}</Alert>
            )}

            <Button
              variant="danger"
              className="mt-3"
              onClick={handleExport}
              disabled={isExporting}
            >
              {isExporting ? 'Exporting...' : 'Export Dataset'}
            </Button>
          </Form>

          {exportHistory.length > 0 && (
            <div className="mt-5">
              <h5>Export History</h5>
              <Table className="data-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Dataset</th>
                    <th>Format</th>
                    <th>Status</th>
                    <th>Download</th>
                  </tr>
                </thead>
                <tbody>
                  {exportHistory.map(item => (
                    <tr key={item.id}>
                      <td>{item.date}</td>
                      <td>{item.datasetName}</td>
                      <td>{item.format}</td>
                      <td>
                        <Badge bg={item.status === 'completed' ? 'success' : 'danger'}>
                          {item.status}
                        </Badge>
                      </td>
                      <td>
                        {item.status === 'completed' && item.downloadUrl ? (
                          <a
                            href={item.downloadUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="btn btn-sm btn-outline-primary"
                          >
                            Download
                          </a>
                        ) : 'N/A'}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </Table>
            </div>
          )}
        </Card.Body>
      </Card>
    </div>
  );
};

export default ExportPage;
