import React, { useState } from 'react';
import { Card, Accordion, Form, InputGroup, Button } from 'react-bootstrap';

const Help: React.FC = () => {
  const [searchTerm, setSearchTerm] = useState('');

  const faqs = [
    {
      question: 'How do I upload a dataset?',
      answer: `To upload a dataset:
1. Go to the Upload page
2. Click "Choose File" or drag and drop your file
3. Select the file format (CSV, Excel, etc.)
4. Configure the import settings
5. Click "Upload" to start the process`,
    },
    {
      question: 'What file formats are supported?',
      answer: 'CleanAid currently supports CSV, Excel (.xlsx, .xls), and JSON file formats. We plan to add support for more formats in future updates.',
    },
    {
      question: 'How does the data cleansing process work?',
      answer: `The data cleansing process involves several steps:
1. Data validation and type checking
2. Handling missing values
3. Removing duplicates
4. Standardizing formats
5. Correcting inconsistencies
6. Flagging potential issues for review`,
    },
    {
      question: 'How do I review flagged records?',
      answer: `To review flagged records:
1. Go to the Review page
2. Use filters to find specific records
3. Click on a record to view details
4. Choose to merge, ignore, or resolve the record
5. Add resolution notes if needed`,
    },
    {
      question: 'Can I export my cleansed data?',
      answer: 'Yes, you can export your cleansed data in CSV or Excel format. Go to the Export page, select your preferred format, and choose whether to export cleansed or flagged data.',
    },
    {
      question: 'How do I manage my account settings?',
      answer: `To manage your account settings:
1. Go to the Settings page
2. Update your profile information
3. Change your password
4. Configure notification preferences
5. Choose your preferred theme`,
    },
  ];

  const filteredFaqs = faqs.filter((faq) =>
    faq.question.toLowerCase().includes(searchTerm.toLowerCase()) ||
    faq.answer.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div className="container-fluid p-4">
      <h2 className="mb-4">Help & Documentation</h2>
      <p className="text-muted mb-4">
        Find answers to common questions and learn how to use CleanAid effectively.
      </p>

      <div className="row">
        <div className="col-md-8">
          <Card className="border-0 shadow-sm mb-4">
            <Card.Body>
              <h4 className="mb-4">Frequently Asked Questions</h4>
              <InputGroup className="mb-4">
                <Form.Control
                  placeholder="Search FAQs..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                />
                <Button variant="outline-secondary">
                  <i className="bi bi-search"></i>
                </Button>
              </InputGroup>

              <Accordion>
                {filteredFaqs.map((faq, index) => (
                  <Accordion.Item key={index} eventKey={index.toString()}>
                    <Accordion.Header>{faq.question}</Accordion.Header>
                    <Accordion.Body>
                      <div className="whitespace-pre-line">{faq.answer}</div>
                    </Accordion.Body>
                  </Accordion.Item>
                ))}
              </Accordion>
            </Card.Body>
          </Card>
        </div>

        <div className="col-md-4">
          <Card className="border-0 shadow-sm mb-4">
            <Card.Body>
              <h4 className="mb-4">Quick Links</h4>
              <div className="list-group list-group-flush">
                <a
                  href="#"
                  className="list-group-item list-group-item-action d-flex align-items-center"
                >
                  <i className="bi bi-file-earmark-text me-2"></i>
                  User Guide
                </a>
                <a
                  href="#"
                  className="list-group-item list-group-item-action d-flex align-items-center"
                >
                  <i className="bi bi-youtube me-2"></i>
                  Video Tutorials
                </a>
                <a
                  href="#"
                  className="list-group-item list-group-item-action d-flex align-items-center"
                >
                  <i className="bi bi-question-circle me-2"></i>
                  Contact Support
                </a>
                <a
                  href="#"
                  className="list-group-item list-group-item-action d-flex align-items-center"
                >
                  <i className="bi bi-github me-2"></i>
                  GitHub Repository
                </a>
              </div>
            </Card.Body>
          </Card>

          <Card className="border-0 shadow-sm">
            <Card.Body>
              <h4 className="mb-4">Need More Help?</h4>
              <p className="text-muted">
                Can't find what you're looking for? Our support team is here to help.
              </p>
              <Button variant="danger" className="w-100">
                <i className="bi bi-envelope me-2"></i>
                Contact Support
              </Button>
            </Card.Body>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default Help; 