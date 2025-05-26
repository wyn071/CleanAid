import React from 'react';
import ReviewData from '../components/ReviewData';
import { Container } from 'react-bootstrap';

const DuplicateReview: React.FC = () => {
  return (
    <Container fluid className="p-0">
      <ReviewData />
    </Container>
  );
};

export default DuplicateReview; 