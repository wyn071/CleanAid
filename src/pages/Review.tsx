import React, { useState, useMemo } from 'react';
import {
  Card, Table, Button, Form, InputGroup, Pagination, Modal,
  Alert, Badge, Tooltip, OverlayTrigger, Row, Col
} from 'react-bootstrap';
import { ProcessingResult, DataIssue, BeneficiaryRecord } from '../services/DataProcessingService';
import '../styles/shared.css';

interface ReviewProps {
  result: ProcessingResult | null;
  onSave: (updatedRecords: BeneficiaryRecord[]) => void;
}

const Review: React.FC<ReviewProps> = ({ result, onSave }) => {
  const [selectedRecord, setSelectedRecord] = useState<BeneficiaryRecord | null>(null);
  const [editedRecord, setEditedRecord] = useState<BeneficiaryRecord | null>(null);
  const [showEditModal, setShowEditModal] = useState(false);
  const [filter, setFilter] = useState('all');
  const [searchTerm, setSearchTerm] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage] = useState(10);
  const [sortConfig, setSortConfig] = useState<{ key: keyof BeneficiaryRecord; direction: 'asc' | 'desc' } | null>(null);
  const [selectedRecords, setSelectedRecords] = useState<Set<string>>(new Set());
  const [bulkAction, setBulkAction] = useState<'approve' | 'reject' | 'delete' | null>(null);
  const [showConfirmModal, setShowConfirmModal] = useState(false);

  const records = result?.records ?? [];
  const issues = result?.issues ?? [];

  const filteredRecords = useMemo(() => {
    let filtered = records.filter(record => {
      const relatedIssues = issues.filter(issue => issue.recordId === record.id);
      if (filter === 'all') return relatedIssues.length > 0;
      return relatedIssues.some(issue => issue.type === filter);
    });

    if (searchTerm) {
      const lower = searchTerm.toLowerCase();
      filtered = filtered.filter(r =>
        r.name.toLowerCase().includes(lower) ||
        r.program.toLowerCase().includes(lower) ||
        r.id.toLowerCase().includes(lower)
      );
    }

    if (sortConfig) {
      filtered.sort((a, b) => {
        const aVal = a[sortConfig.key];
        const bVal = b[sortConfig.key];
        if (aVal < bVal) return sortConfig.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return sortConfig.direction === 'asc' ? 1 : -1;
        return 0;
      });
    }

    return filtered;
  }, [records, issues, filter, searchTerm, sortConfig]);

  const totalPages = Math.ceil(filteredRecords.length / itemsPerPage);
  const currentRecords = filteredRecords.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

  const toggleSelectAll = (checked: boolean) => {
    setSelectedRecords(checked ? new Set(currentRecords.map(r => r.id)) : new Set());
  };

  const toggleSelectOne = (id: string, checked: boolean) => {
    const updated = new Set(selectedRecords);
    checked ? updated.add(id) : updated.delete(id);
    setSelectedRecords(updated);
  };

  const getIssueBadge = (issue: DataIssue) => {
    const variant = {
      high: 'danger',
      medium: 'warning',
      low: 'info'
    }[issue.severity] || 'secondary';

    return (
      <OverlayTrigger placement="top" overlay={<Tooltip>{issue.description}</Tooltip>}>
        <Badge bg={variant}>{issue.type}</Badge>
      </OverlayTrigger>
    );
  };

  const handleSort = (key: keyof BeneficiaryRecord) => {
    setSortConfig(prev =>
      prev?.key === key
        ? { key, direction: prev.direction === 'asc' ? 'desc' : 'asc' }
        : { key, direction: 'asc' }
    );
  };

  const handleBulkAction = (action: 'approve' | 'reject' | 'delete') => {
    setBulkAction(action);
    setShowConfirmModal(true);
  };

  const confirmBulkAction = () => {
    if (!bulkAction) return;
    const updated = records.map(r =>
      selectedRecords.has(r.id)
        ? { ...r, status: bulkAction === 'approve' ? 'active' : bulkAction === 'reject' ? 'inactive' : 'deleted' }
        : r
    );
    onSave(updated);
    setSelectedRecords(new Set());
    setShowConfirmModal(false);
  };

  const handleEdit = (record: BeneficiaryRecord) => {
    setEditedRecord({ ...record });
    setShowEditModal(true);
  };

  const saveEdit = () => {
    if (!editedRecord) return;
    const updated = records.map(r => (r.id === editedRecord.id ? editedRecord : r));
    onSave(updated);
    setShowEditModal(false);
  };

  if (!result || records.length === 0) {
    return <Alert variant="warning">No data to review. Please upload a dataset first.</Alert>;
  }

  return (
    <div className="container-fluid">
      <Card>
        <Card.Body>
          <div className="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h4>Review Records</h4>
              <p className="text-muted mb-0">Flagged issues in uploaded datasets</p>
            </div>
            <div>
              <Button variant="danger" size="sm" onClick={() => onSave(records)}>Save All</Button>
            </div>
          </div>

          <Row className="mb-3 g-2">
            <Col md={4}>
              <Form.Select value={filter} onChange={e => setFilter(e.target.value)}>
                <option value="all">All Issues</option>
                <option value="duplicate">Duplicate</option>
                <option value="missing">Missing</option>
                <option value="mismatch">Mismatch</option>
              </Form.Select>
            </Col>
            <Col md={4}>
              <InputGroup>
                <InputGroup.Text><i className="bi bi-search"></i></InputGroup.Text>
                <Form.Control value={searchTerm} onChange={e => setSearchTerm(e.target.value)} placeholder="Search..." />
              </InputGroup>
            </Col>
            <Col md={4} className="text-end text-muted">
              Showing {filteredRecords.length} of {records.length}
            </Col>
          </Row>

          <Table striped bordered hover responsive>
            <thead>
              <tr>
                <th><Form.Check checked={selectedRecords.size === currentRecords.length} onChange={e => toggleSelectAll(e.target.checked)} /></th>
                <th onClick={() => handleSort('id')}>ID</th>
                <th onClick={() => handleSort('name')}>Name</th>
                <th onClick={() => handleSort('program')}>Program</th>
                <th onClick={() => handleSort('amount')}>Amount</th>
                <th>Status</th>
                <th>Issues</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {currentRecords.map(r => {
                const relatedIssues = issues.filter(i => i.recordId === r.id);
                return (
                  <tr key={r.id}>
                    <td><Form.Check checked={selectedRecords.has(r.id)} onChange={e => toggleSelectOne(r.id, e.target.checked)} /></td>
                    <td>{r.id}</td>
                    <td>{r.name}</td>
                    <td>{r.program}</td>
                    <td>₱{r.amount?.toLocaleString()}</td>
                    <td><Badge bg="secondary">{r.status}</Badge></td>
                    <td className="d-flex flex-wrap gap-1">
                      {relatedIssues.map((issue, i) => <React.Fragment key={i}>{getIssueBadge(issue)}</React.Fragment>)}
                    </td>
                    <td>
                      <Button size="sm" variant="outline-primary" onClick={() => handleEdit(r)}>Edit</Button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </Table>

          <Pagination className="justify-content-center">
            <Pagination.First onClick={() => setCurrentPage(1)} disabled={currentPage === 1} />
            <Pagination.Prev onClick={() => setCurrentPage(p => Math.max(1, p - 1))} disabled={currentPage === 1} />
            {[...Array(totalPages)].map((_, i) => (
              <Pagination.Item key={i + 1} active={i + 1 === currentPage} onClick={() => setCurrentPage(i + 1)}>
                {i + 1}
              </Pagination.Item>
            ))}
            <Pagination.Next onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))} disabled={currentPage === totalPages} />
            <Pagination.Last onClick={() => setCurrentPage(totalPages)} disabled={currentPage === totalPages} />
          </Pagination>

          {selectedRecords.size > 0 && (
            <div className="text-end mt-3">
              <Button variant="success" size="sm" onClick={() => handleBulkAction('approve')} className="me-2">Approve</Button>
              <Button variant="warning" size="sm" onClick={() => handleBulkAction('reject')} className="me-2">Reject</Button>
              <Button variant="danger" size="sm" onClick={() => handleBulkAction('delete')}>Delete</Button>
            </div>
          )}
        </Card.Body>
      </Card>

      <Modal show={showEditModal} onHide={() => setShowEditModal(false)}>
        <Modal.Header closeButton><Modal.Title>Edit Record</Modal.Title></Modal.Header>
        <Modal.Body>
          {editedRecord && (
            <Form>
              <Form.Group className="mb-3">
                <Form.Label>Name</Form.Label>
                <Form.Control value={editedRecord.name} onChange={e => setEditedRecord({ ...editedRecord, name: e.target.value })} />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>Program</Form.Label>
                <Form.Control value={editedRecord.program} onChange={e => setEditedRecord({ ...editedRecord, program: e.target.value })} />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>Amount</Form.Label>
                <Form.Control type="number" value={editedRecord.amount ?? ''} onChange={e => setEditedRecord({ ...editedRecord, amount: parseFloat(e.target.value) })} />
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>Status</Form.Label>
                <Form.Select value={editedRecord.status} onChange={e => setEditedRecord({ ...editedRecord, status: e.target.value })}>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="pending">Pending</option>
                </Form.Select>
              </Form.Group>
            </Form>
          )}
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setShowEditModal(false)}>Cancel</Button>
          <Button variant="primary" onClick={saveEdit}>Save</Button>
        </Modal.Footer>
      </Modal>

      <Modal show={showConfirmModal} onHide={() => setShowConfirmModal(false)}>
        <Modal.Header closeButton><Modal.Title>Confirm Bulk Action</Modal.Title></Modal.Header>
        <Modal.Body>
          Are you sure you want to <strong>{bulkAction}</strong> {selectedRecords.size} selected record(s)?
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setShowConfirmModal(false)}>Cancel</Button>
          <Button variant="danger" onClick={confirmBulkAction}>Confirm</Button>
        </Modal.Footer>
      </Modal>
    </div>
  );
};

export default Review;
