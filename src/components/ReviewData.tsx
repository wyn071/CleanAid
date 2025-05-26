import React, { useState } from 'react';
import { 
  Container, 
  Row, 
  Col, 
  Form, 
  Button, 
  Table, 
  Badge,
  InputGroup
} from 'react-bootstrap';
import { FaSearch, FaCheck, FaTrash } from 'react-icons/fa';

interface DuplicateRecord {
  id: string;
  name: string;
  dateOfBirth: string;
  duplicateType: 'exact' | 'similar' | 'potential';
  status: 'pending' | 'reviewed' | 'resolved' | 'deleted';
  confidence: number;
}

const ReviewData: React.FC = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [duplicateType, setDuplicateType] = useState('all');
  const [status, setStatus] = useState('all');
  const [selectedRecords, setSelectedRecords] = useState<string[]>([]);

  // Mock data - replace with actual data from your backend
  const [records] = useState<DuplicateRecord[]>([
    {
      id: '1',
      name: 'John Doe',
      dateOfBirth: '1990-01-01',
      duplicateType: 'exact',
      status: 'pending',
      confidence: 0.95
    },
    // Add more mock records as needed
  ]);

  const getDuplicateTypeBadge = (type: string) => {
    const variants = {
      exact: 'primary',
      similar: 'warning',
      potential: 'info'
    };
    return <Badge bg={variants[type as keyof typeof variants]}>{type}</Badge>;
  };

  const getStatusBadge = (status: string) => {
    const variants = {
      pending: 'warning',
      reviewed: 'info',
      resolved: 'success',
      deleted: 'danger'
    };
    return <Badge bg={variants[status as keyof typeof variants]}>{status}</Badge>;
  };

  const handleSelectRecord = (id: string) => {
    setSelectedRecords(prev => 
      prev.includes(id) 
        ? prev.filter(recordId => recordId !== id)
        : [...prev, id]
    );
  };

  const handleSelectAll = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.checked) {
      setSelectedRecords(records.map(record => record.id));
    } else {
      setSelectedRecords([]);
    }
  };

  return (
    <Container fluid className="p-4">
      {/* Header */}
      <div className="mb-4">
        <h2 className="fw-bold">Review Flagged Data</h2>
        <p className="text-muted">Review and manage potential duplicate records in the system</p>
      </div>

      {/* Filter Bar */}
      <Row className="mb-4">
        <Col md={4}>
          <InputGroup>
            <InputGroup.Text>
              <FaSearch />
            </InputGroup.Text>
            <Form.Control
              placeholder="Search records..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
          </InputGroup>
        </Col>
        <Col md={3}>
          <Form.Select
            value={duplicateType}
            onChange={(e) => setDuplicateType(e.target.value)}
          >
            <option value="all">All Duplicate Types</option>
            <option value="exact">Exact Matches</option>
            <option value="similar">Similar Matches</option>
            <option value="potential">Potential Matches</option>
          </Form.Select>
        </Col>
        <Col md={3}>
          <Form.Select
            value={status}
            onChange={(e) => setStatus(e.target.value)}
          >
            <option value="all">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="reviewed">Reviewed</option>
            <option value="resolved">Resolved</option>
            <option value="deleted">Deleted</option>
          </Form.Select>
        </Col>
        <Col md={2}>
          <Button variant="primary" className="w-100">
            Apply Filters
          </Button>
        </Col>
      </Row>

      {/* Table */}
      <Table responsive hover className="mb-4">
        <thead>
          <tr>
            <th>
              <Form.Check
                type="checkbox"
                onChange={handleSelectAll}
                checked={selectedRecords.length === records.length}
              />
            </th>
            <th>Record ID</th>
            <th>Name</th>
            <th>Date of Birth</th>
            <th>Duplicate Type</th>
            <th>Status</th>
            <th>Confidence</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {records.map((record) => (
            <tr key={record.id}>
              <td>
                <Form.Check
                  type="checkbox"
                  checked={selectedRecords.includes(record.id)}
                  onChange={() => handleSelectRecord(record.id)}
                />
              </td>
              <td>{record.id}</td>
              <td>{record.name}</td>
              <td>{record.dateOfBirth}</td>
              <td>{getDuplicateTypeBadge(record.duplicateType)}</td>
              <td>{getStatusBadge(record.status)}</td>
              <td>{(record.confidence * 100).toFixed(1)}%</td>
              <td>
                <Button variant="outline-primary" size="sm" className="me-2">
                  <FaCheck /> Review
                </Button>
                <Button variant="outline-danger" size="sm">
                  <FaTrash /> Delete
                </Button>
              </td>
            </tr>
          ))}
        </tbody>
      </Table>

      {/* Bulk Actions */}
      <Row className="mt-4">
        <Col md={6}>
          <Button 
            variant="success" 
            size="lg" 
            className="w-100"
            disabled={selectedRecords.length === 0}
          >
            <FaCheck className="me-2" />
            Mark Selected as Resolved
          </Button>
        </Col>
        <Col md={6}>
          <Button 
            variant="danger" 
            size="lg" 
            className="w-100"
            disabled={selectedRecords.length === 0}
          >
            <FaTrash className="me-2" />
            Delete Selected Duplicates
          </Button>
        </Col>
      </Row>
    </Container>
  );
};

export default ReviewData; 