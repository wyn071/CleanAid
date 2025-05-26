import React from 'react';
import { Modal, Button, Table, Form } from 'react-bootstrap';

interface MergeModalProps {
  show: boolean;
  records: any[];
  onMerge: (merged: any) => void;
  onClose: () => void;
}

const MergeModal: React.FC<MergeModalProps> = ({ show, records, onMerge, onClose }) => {
  const fields = Object.keys(records[0] || {});
  const [mergedRecord, setMergedRecord] = React.useState<any>({});

  const handleSelect = (field: string, value: any) => {
    setMergedRecord((prev: any) => ({ ...prev, [field]: value }));
  };

  return (
    <Modal show={show} onHide={onClose} size="lg">
      <Modal.Header closeButton>
        <Modal.Title>Merge Records</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <Table bordered>
          <thead>
            <tr>
              <th>Field</th>
              {records.map((_, i) => <th key={i}>Record {i + 1}</th>)}
              <th>Selected</th>
            </tr>
          </thead>
          <tbody>
            {fields.map((field) => (
              <tr key={field}>
                <td>{field}</td>
                {records.map((record, i) => (
                  <td key={i}>
                    <Form.Check
                      type="radio"
                      name={field}
                      id={`${field}-${i}`}
                      label={record[field]}
                      onChange={() => handleSelect(field, record[field])}
                    />
                  </td>
                ))}
                <td>{mergedRecord[field] || <span className="text-muted">Not selected</span>}</td>
              </tr>
            ))}
          </tbody>
        </Table>
      </Modal.Body>
      <Modal.Footer>
        <Button variant="secondary" onClick={onClose}>Cancel</Button>
        <Button
          variant="danger"
          onClick={() => {
            onMerge(mergedRecord);
            onClose();
          }}
        >
          Confirm Merge
        </Button>
      </Modal.Footer>
    </Modal>
  );
};

export default MergeModal;
