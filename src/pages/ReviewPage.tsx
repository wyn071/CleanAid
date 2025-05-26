import React, { useState } from 'react';
import {
  Card, Table, Button, Form, InputGroup, Pagination, Badge, Row, Col
} from 'react-bootstrap';
import '../styles/shared.css';
import MergeModal from '../components/MergeModal';
import { BeneficiaryRecord } from '../services/DataProcessingService';


interface FlaggedRecordItem extends BeneficiaryRecord {
  issueType: string;
  issueDescription: string;
  status: 'Pending' | 'Resolved' | 'Ignored' | 'Merged';
  date: string; // 🔍 Ensure this is declared as string
}

const ReviewPage: React.FC = () => {
  const [flaggedRecords, setFlaggedRecords] = useState<FlaggedRecordItem[]>([
    {
      id: 'rec1',
      name: 'Juan Dela Cruz',
      program: 'SAP',
      amount: 5000,
      contact: '09123456789',
      address: 'Cagayan de Oro City',
      issueType: 'Duplicate',
      issueDescription: 'Same ID found',
      status: 'Pending',
      date: new Date('2025-05-01').toISOString() // ✅ Converted to string
    },
    {
      id: 'rec2',
      name: 'Juan Dela Cruz',
      program: 'SAP',
      amount: 5000,
      contact: '09123456789',
      address: 'Cagayan de Oro City',
      issueType: 'Duplicate',
      issueDescription: 'Same ID found',
      status: 'Pending',
      date: new Date('2025-05-01').toISOString()
    },
    {
      id: 'rec3',
      name: 'Maria Santos',
      program: '4Ps',
      amount: 3000,
      contact: '',
      address: 'Cagayan de Oro City',
      issueType: 'Missing',
      issueDescription: 'Missing contact',
      status: 'Pending',
      date: new Date('2025-05-02').toISOString()
    }
  ]);


  const [searchTerm, setSearchTerm] = useState('');
  const [filterType, setFilterType] = useState('');
  const [filterStatus, setFilterStatus] = useState('');
  const [selectedRecords, setSelectedRecords] = useState<string[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const recordsPerPage = 10;
  const [showMergeModal, setShowMergeModal] = useState(false);
  const [recordsToMerge, setRecordsToMerge] = useState<FlaggedRecordItem[]>([]);

  const filteredRecords = flaggedRecords.filter(record => {
    return (
      record.address.toLowerCase().includes('cagayan de oro') &&
      (searchTerm === '' || record.name.toLowerCase().includes(searchTerm.toLowerCase())) &&
      (filterType === '' || record.issueType.toLowerCase() === filterType.toLowerCase()) &&
      (filterStatus === '' || record.status === filterStatus)
    );
  });

  const currentRecords = filteredRecords.slice((currentPage - 1) * recordsPerPage, currentPage * recordsPerPage);
  const totalPages = Math.ceil(filteredRecords.length / recordsPerPage);

  const handleSelectAll = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSelectedRecords(e.target.checked ? currentRecords.map(r => r.id) : []);
  };

  const handleSelectRecord = (e: React.ChangeEvent<HTMLInputElement>, id: string) => {
    setSelectedRecords(prev =>
      e.target.checked ? [...prev, id] : prev.filter(r => r !== id)
    );
  };

  const getIssueBadge = (type: string) => {
    const variant = {
      duplicate: 'warning',
      missing: 'danger',
      mismatch: 'info',
      merged: 'secondary'
    }[type.toLowerCase()] || 'secondary';

    return <Badge bg={variant}>{type}</Badge>;
  };

  const handleMergeClick = (target: FlaggedRecordItem) => {
    const duplicates = flaggedRecords.filter(
      r => r.name === target.name && r.issueType === 'Duplicate' && r.status === 'Pending'
    );
    setRecordsToMerge(duplicates);
    setShowMergeModal(true);
  };

  const handleMerge = (merged: any) => {
    const newId = `merged_${Date.now()}`;
    const newRecord: FlaggedRecordItem = {
      ...merged,
      id: newId,
      issueType: 'Merged',
      issueDescription: 'Merged record',
      status: 'Resolved'
    };

    setFlaggedRecords(prev =>
      [newRecord, ...prev.filter(r => !recordsToMerge.some(d => d.id === r.id))]
    );
    setShowMergeModal(false);
  };

  return (
    <div className="container-fluid py-4">
      <Card className="data-card">
        <Card.Body>
          <h4 className="mb-3">Review Flagged Data</h4>

          <Row className="mb-3 g-3">
            <Col md={4}>
              <InputGroup>
                <Form.Control
                  placeholder="Search by name..."
                  value={searchTerm}
                  onChange={e => setSearchTerm(e.target.value)}
                />
              </InputGroup>
            </Col>
            <Col md={3}>
              <Form.Select value={filterType} onChange={e => setFilterType(e.target.value)}>
                <option value="">All Issue Types</option>
                <option value="duplicate">Duplicate</option>
                <option value="missing">Missing Data</option>
                <option value="mismatch">Mismatch</option>
              </Form.Select>
            </Col>
            <Col md={3}>
              <Form.Select value={filterStatus} onChange={e => setFilterStatus(e.target.value)}>
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Resolved">Resolved</option>
                <option value="Ignored">Ignored</option>
              </Form.Select>
            </Col>
          </Row>

          <Table striped bordered hover responsive>
            <thead>
              <tr>
                <th><Form.Check type="checkbox" onChange={handleSelectAll} checked={selectedRecords.length === currentRecords.length && currentRecords.length > 0} /></th>
                <th>ID</th>
                <th>Name</th>
                <th>Program</th>
                <th>Amount</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Issue</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {currentRecords.map(record => (
                <tr key={record.id}>
                  <td><Form.Check type="checkbox" checked={selectedRecords.includes(record.id)} onChange={(e) => handleSelectRecord(e, record.id)} /></td>
                  <td>{record.id}</td>
                  <td>{record.name}</td>
                  <td>{record.program}</td>
                  <td>₱{record.amount?.toLocaleString()}</td>
                  <td>{record.contact}</td>
                  <td>{record.address}</td>
                  <td>{getIssueBadge(record.issueType)}</td>
                  <td>{record.status}</td>
                  <td>
                    <Button size="sm" variant="outline-primary" className="me-1">View</Button>
                    <Button size="sm" variant="outline-success" className="me-1">Resolve</Button>
                    {record.issueType === 'Duplicate' && record.status === 'Pending' && (
                      <Button
                        size="sm"
                        variant="outline-warning"
                        className="me-1"
                        onClick={() => handleMergeClick(record)}
                      >
                        Merge
                      </Button>
                    )}
                    <Button size="sm" variant="outline-danger">Ignore</Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </Table>

          {totalPages > 1 && (
            <Pagination className="justify-content-center">
              <Pagination.Prev onClick={() => setCurrentPage(p => Math.max(p - 1, 1))} disabled={currentPage === 1} />
              <Pagination.Item active>{currentPage}</Pagination.Item>
              <Pagination.Next onClick={() => setCurrentPage(p => Math.min(p + 1, totalPages))} disabled={currentPage === totalPages} />
            </Pagination>
          )}

          <MergeModal
            show={showMergeModal}
            records={recordsToMerge}
            onMerge={handleMerge}
            onClose={() => setShowMergeModal(false)}
          />
        </Card.Body>
      </Card>
    </div>
  );
};

export default ReviewPage;
